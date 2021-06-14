<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Gsferro\TranslationSolutionEasy\Models\TranslationSolutionEasy;
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
        $this->langsSupport = config('laravellocalization.supportedLocales');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // validation
        if (count($this->langsSupport) == 0) {
            return $this->error('Sorry, not language config in your application.');
        }

        if (count($this->langsSupport) == 1 && current($this->langsSupport) == $this->locale) {
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
        $options = $this->options();

        if (!empty($options)) {
            $table  = $this->option('table') ?? $this->ask('What is table name?');
            // validação
            if ($this->tableDontExist($table)) {
                return $this->error('Oops, table not found.');
            }

            $column = $this->option('column') ?? $this->ask('What is column name?');
            if ($this->CollumnDontExist($table, $column)) {
                return $this->error('Oops, column not found.');
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
        $columns = DB::select("DESC {$table}");

        return !collect($columns)->contains("Field", $column);
    }

    private function exec(array $tables)
    {
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
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Oops..{$e->getMessage()}");
        }

        $bar->finish();
    }

    /**
     * @param array $column
     * @param $table
     */
    private function translateAndPersist(array $column, $table): void
    {
        // busca os dados
        $rows = DB::select("select {$column} from {$table}");
        collect($rows)->map(function ($row) use ($column) {
            // translate
            $trans = reversotranslation($this->locale, $this->langsSupport)->trans($row);
            if ($trans[ "success" ]) {

                $key = TranslationSolutionEasy::key($column)->group();
                if ($key->exists()) {$text = $key->first()->text;}

                TranslationSolutionEasy::updateOrCreate([
                    'group' => '*',
                    'key'   => $column,
                ],[
                    'text' => array_merge($text ?? [] , $trans[ "translate" ]),
                ]);
            }
        });
    }
}