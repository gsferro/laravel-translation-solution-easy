<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Gsferro\TranslationSolutionEasy\Models\TranslationSolutionEasy;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TranslationTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:translate-tables {--tbl|table= : Table name} {--col|column= : Collumn name}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate the values contained within the database tables!';
    /** * @var Repository */
    private $langsSupport;
    /** * @var Repository */
    private $locale;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->locale       = config('app.locale');
        $this->langsSupport = array_keys(config('laravellocalization.supportedLocales'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        |---------------------------------------------------
        | validation
        |---------------------------------------------------
        */
        if (count($this->langsSupport) == 0) {
            $this->line("");
            return $this->error('Sorry, not language config in your application.');
        }

        if (count($this->langsSupport) == 1 && in_array($this->locale, $this->langsSupport)) {
            $this->info('Attention! The configured language is already in your application.');
            if ($this->confirm('Abortion the translation for you set up?!')) {
                return $this->info('Okay. Abort this!');
            }
        }

        /*
        |---------------------------------------------------
        | caso passe algum paramentro
        |---------------------------------------------------
        */
        if (!empty($this->option('table')) || !empty($this->option('column'))) {
            $table = $this->option('table') ?? $this->ask('What is table name?');
            // validação
            if ($this->tableDontExist($table)) {
                $this->line("");
                return $this->error("Oops, table [ {$table} ] not found.");
            }

            $column = $this->option('column') ?? $this->ask('What is column name?');
            if ($this->CollumnDontExist($table, $column)) {
                $this->line("");
                return $this->error("Oops, column [ {$column} ]  not found.");
            }

            return $this->exec([$table => $column]);
        }

        /*
        |---------------------------------------------------
        | Pegando da configuração
        |---------------------------------------------------
        */
        $tables = config('translationsolutioneasy.translate-tables');
        if (empty($tables)) {
            $this->line("");
            return $this->error('Oops... Not tables and columns config in translationsolutioneasy.translate-tables.');
        }

        return $this->exec($tables);
    }

    private function tableDontExist($table)
    {
        return !Schema::hasTable($table);
    }

    private function CollumnDontExist($table, $column)
    {
        if (env('DB_CONNECTION') == 'sqlite') {
            $columns = DB::select("pragma table_info('{$table}')");
            return !collect($columns)->contains("name", $column);
        }

        $columns = DB::select("pragma table_info('{$table}')");
        return !collect($columns)->contains("Field", $column);
    }

    private function exec(array $tables)
    {
        $this->line("");
        $this->line("Language from locale app: [ {$this->locale} ]");
        $this->line("");
        $this->line("Languages that will be translated together: [ " . implode(" | ", $this->langsSupport) . " ]");
        $this->line("");
        $this->line("Total of tables");
        $bar = $this->output->createProgressBar(count($tables));

        $bar->start();

        try {
            DB::beginTransaction();

            foreach ($tables as $table => $column) {
                if (is_array($column)) {
                    foreach ($column as $col) {
                        $this->translateAndPersist($col, $table);
                    }
                } else {
                    $this->translateAndPersist($column, $table);
                }

                $bar->advance();
            }
            DB::commit();

            $bar->finish();
            $this->line("");
            $this->comment("\7");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Oops.. {$e->getMessage()}");
        }
    }

    /**
     * @param array $column
     * @param $table
     */
    private function translateAndPersist($column, $table)
    {
        // busca os dados
        $rows = DB::select("select {$column} from {$table}");

        $this->line("");
        $this->line("");
        $this->line("Total registres per table [ {$table} ] with column [ {$column} ] ");
        $col = $this->output->createProgressBar(count($rows));


        $translation = new ReversoTranslation($this->locale, $this->langsSupport);
        collect($rows)->map(function ($row) use ($column, $translation, $col) {
            // translate
            $key   = $row->$column;
            $trans = $translation->trans($key);

            if ($trans[ "success" ]) {

                $model = TranslationSolutionEasy::key($key)->group();
                if ($model->exists()) {
                    $text = $model->first()->text;
                }
                TranslationSolutionEasy::updateOrCreate([
                    'group' => '*',
                    'key'   => $key,
                ], [
                    'text' => array_merge($text ?? [], $trans[ "translate" ]),
                ]);
            }
            $col->advance();
        });
        $col->finish();
        $this->line("");
    }
}
