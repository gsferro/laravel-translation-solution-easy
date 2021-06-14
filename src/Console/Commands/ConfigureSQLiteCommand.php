<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Illuminate\Console\Command;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
        */

        $table = $this->option('database') ?? $this->askWithCompletion('What is database name?', ['translate-solution-easy', 'database'], 'database');

        if (!$this->confirm("Are sure you want to create the database: {$table}.sqlite")) {
            return $this->info('Okay. Abort this!');
        }

        /*
        |---------------------------------------------------
        | verifica se existe
        |---------------------------------------------------
        */

        if (
            file_exists(database_path("{$table}.sqlite")) &&
            !$this->confirm("This database already exists. Do you want to overwrite?")
        ) {
            return $this->info('Okay. Abort this!');
        }

        fopen(database_path("{$table}.sqlite"), 'w+');

        if (file_exists(database_path("{$table}.sqlite"))) {
            return $this->info('Created with success!');
        }

        return $this->info('Fail in Created!');
    }

}
