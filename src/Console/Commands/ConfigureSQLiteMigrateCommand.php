<?php

namespace Gsferro\TranslationSolutionEasy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ConfigureSQLiteMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsferro:configure-sqlite-migrate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate from new settings the database SQLite';

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
        if (!$this->confirm('Run migrate in your new configuration?!', true)) {
            return $this->info('Okay. Not run migrate.');
        }

        $this->call('migrate', [
            "--database" => config('translationsolutioneasy.connection-sqlite'),
            "--path"     => "database/migrations/translation"
        ]);

        return $this->comment('Thanks for using me!');
    }
}
