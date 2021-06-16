<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;

class ConfigureSQLiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:configure-sqlite {--database= : Database name} ';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Easilyn configuretion from use database SQLite';

    private $datatable;
    private $nameConfig;
    private $configNameSqlite = null;
    private $app;

    /**
     * Create a new command instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->nameConfig = ":database-sqlite";

        $this->app = $app ;
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
        | Pegando o nome da base de dados
        |---------------------------------------------------
        |
        | Não pode usar sqlite como name
        */

        $this->datatable = $this->nameDifSqlite();

        /*
        |---------------------------------------------------
        | Caso escolha database (default)
        |---------------------------------------------------
        |
        | Não irá alterar o config/database
        | Ira colocar sqlite no config/translationsolutioneasy
        */
        if ($this->datatable == "database") {
            $this->configNameSqlite = "sqlite";
        }

        /*
        |---------------------------------------------------
        | Encapsula o name
        |---------------------------------------------------
        */
        $databaseName = "{$this->datatable}.sqlite";

        /*
        |---------------------------------------------------
        | Confirm created
        |---------------------------------------------------
        */
        if (!$this->confirm("Are sure you want to create the database: {$databaseName}", true)) {
            return $this->comment('Okay. Abort this!');
        }

        /*
        |---------------------------------------------------
        | Verifica se existe
        |---------------------------------------------------
        |
        | Se não quiser dar overwrite continua para updateConfig
        */
        if (
            file_exists(database_path($databaseName)) &&
            !$this->confirm("This database already exists. Do you want to overwrite?")
        ) {
            $this->info('Okay. Dont overwrite!');

            return $this->updateConfig();
        }

        /*
        |---------------------------------------------------
        | Caso queira fazer o overwrite
        |---------------------------------------------------
        |
        | Cria database e avança para updateConfig
        */
        fopen(database_path($databaseName), 'w+');

        if (!file_exists(database_path($databaseName))) {
            return $this->comment("Fail in Created {$databaseName}!");
        }

        $this->info("Created {$databaseName} with success!");

        return $this->updateConfig();
    }

    /**
    |---------------------------------------------------
    | Atualização configs
    |---------------------------------------------------
    |
    | Colocando a configuração em config/database caso seja
    | diferente de database (default)
    |
    | Atualizando em config/translationsolutioneasy
     */
    private function updateConfig()
    {
        if (!$this->confirm('Update your config/database and the model this package?!', true)) {
            $this->info('Okay. Not Update.');
            return $this->comment('Thanks for using me!');
        }

        try {
            /*
            |---------------------------------------------------
            | O padrão do laravel já é database
            |---------------------------------------------------
            |
            | Não precisa atualizar
            */
            if ($this->datatable != "database") {
                $this->mergin('database');
                $this->comment('Update config/database.');
            }

            $this->mergin('translationsolutioneasy');
            $this->comment('Update config/translationsolutioneasy.');

            /*
            //Reload the cached config
            if (file_exists(App::getCachedConfigPath())) {
                Artisan::call("config:cache");
            }
            */
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        return $this->comment('Thanks for using me!');
    }

    private function mergin($key)
    {
        $path = __DIR__ . "/../../config/sqlite-{$key}.php";
        if ($this->mergeConfigFrom($path, $key) === false) {
            throw new Exception("$key dont exists in your configs!");
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $newConfig
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($newConfig, $key)
    {
        $config = $this->app['config']->get($key, []);
        if (!$config) {
            return false;
        }
        // ajusta a config com o database informado
        $newConfig  = $this->transforme(require $newConfig);

        $this->app['config']->set($key, $this->mergeConfig($newConfig, $config));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array  $original
     * @param  array  $merging
     * @return array
     */
    protected function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($merging, $original);

        foreach ($original as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (! Arr::exists($merging, $key)) {
                continue;
            }

            if (is_numeric($key)) {
                continue;
            }

            $array[$key] = $this->mergeConfig($value, $merging[$key]);
        }

        return $array;
    }

    /**
     * Recebe o array, transofrma em json para buscar usando replace e transformar
     *
     * @param array $config
     * @return array
     */
    private function transforme(array $config) : array
    {
        return json_decode(
            str_replace($this->nameConfig, $this->configNameSqlite ?? $this->datatable, json_encode($config))
            , true);
    }

    /**
     * @param bool $reply
     * @return array|bool|mixed|string|null
     */
    private function nameDifSqlite($reply = false)
    {
        $ask    = 'What is database name?';
        $option = $this->option('database');
        if ($reply) {
            $this->error('Dont use sqlite from name!');
            $ask = 'What is other database name?';
            unset($option);
        }

        $name = $option ??
            $this->askWithCompletion($ask, [
                'translate-solution-easy',
                'database'
            ], 'database');

        if ($name == "sqlite") {
            return $this->nameDifSqlite(true);
        }

        return $name;
    }

}