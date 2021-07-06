<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Exception;
use Gsferro\TranslationSolutionEasy\Interfaces\TranslationCommandInterface;
use Gsferro\TranslationSolutionEasy\Traits\TranslationCommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TranslationTablesCommand extends Command implements TranslationCommandInterface
{
    use TranslationCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:translate-tables {--table= : Table name} {--column= : Collumn name}  {--lang= : Language}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate the values contained within the database tables!';

    /**
     * @var array
     */
    private $tables;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setLangs();
        $this->messageFinish = "gsferro:translate-files";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            /*
            |---------------------------------------------------
            | validation
            |---------------------------------------------------
            */
            // validation generic
            $this->validation();

            /*
            |---------------------------------------------------
            | caso passe algum paramentro
            |---------------------------------------------------
            */
            if (!empty($this->option('table')) || !empty($this->option('column'))) {
                return $this->optionInline();
            }

            /*
            |---------------------------------------------------
            | Pegando da configuração
            |---------------------------------------------------
            */
            $tables = config('translationsolutioneasy.translate-tables');
            if (empty($tables)) {
                throw new Exception('Oops... Not tables and columns config in translationsolutioneasy.translate-tables.');
            }

            $this->tables = $tables;

            /*
            |---------------------------------------------------
            | executando tradução
            |---------------------------------------------------
            */
            $this->exec();
        } catch (\Exception $e) {
            return $this->comment($e->getMessage());
        }
    }

    private function optionInline()
    {
        $table = $this->option('table') ?? $this->ask('What is table name');
        // validação
        if ($this->tableDontExist($table)) {
            throw new Exception("Oops, table [ {$table} ] not found.");
        }

        $collunsChoice = $this->collunsChoice($table);
        $column        = $this->option('column') ??
            $this->choice('What is column name',
                $collunsChoice,
                null,
                count($collunsChoice),
                false
            );

        if ($this->CollumnDontExist($table, $column)) {
            throw new Exception("Oops, column [ {$column} ]  not found.");
        }

        $this->tables = [$table => $column];

        return $this->exec();
    }

    private function tableDontExist($table)
    {
        return !Schema::hasTable($table);
    }

    private function collunsChoice($table)
    {
        $desc    = $this->describleTable($table);
        $key     = $desc[ "key" ];
        $columns = $desc[ "columns" ];

        $choices = [];
        foreach ($columns as $column) {
            $choices[] = $column->$key;
        }
        return $choices;
    }

    private function collumnDontExist($table, $column)
    {
        return !in_array($column, $this->collunsChoice($table));
    }

    /**
     * @param string $lang
     * @throws Exception
     */
    public function execInCommand(string $lang)
    {
        $this->line("");
        $this->line("Total of tables for lang [ $lang ]:");
        $bar = $this->output->createProgressBar(count($this->tables));
        $bar->start();

        foreach ($this->tables as $table => $column) {
            if (is_array($column)) {
                foreach ($column as $col) {
                    $this->translateAndPersist($lang, $col, $table);
                }
            } else {
                $this->translateAndPersist($lang, $column, $table);
            }

            $bar->advance();
        }
        $bar->finish();
        $this->line("");
        $this->comment("Finish tables in lang [ $lang ]");
    }

    /**
     * @param string $lang
     * @param array $column
     * @param string $table
     */
    private function translateAndPersist(string $lang, $column, string $table)
    {
        // busca os dados
        $rows = DB::select("select {$column} from {$table}");

        $this->line("");
        $this->line("");
        $this->line("Total registres per table [ {$table} ] with column [ {$column} ] in lang [ $lang ]:");
        $col = $this->output->createProgressBar(count($rows));
        $col->start();

        collect($rows)->map(function ($row) use ($lang, $column, $col) {
            // key and translate
            $key = $row->$column;

            $this->persist($lang, $key, $key);

            $col->advance();
        });
        $col->finish();
        $this->line("");
        $this->comment("Finish table [ {$table} ] in lang [ $lang ]");
    }

    /**
     * @param $table
     * @return array
     */
    private function describleTable($table): array
    {
        switch (env('DB_CONNECTION')) {
            case 'sqlite':
                $columns = DB::select("pragma table_info('{$table}')");
                $key     = "name";
            break;

            case 'sqlsrv':
                $columns = DB::select("EXEC sp_columns {$table}");
                $key     = "COLUMN_NAME";
            break;

            default:
                $columns = DB::select("DESC {$table}");
                $key     = "Field";
            break;
        }

        return [
            "key"     => $key,
            "columns" => $columns,
        ];
    }
}
