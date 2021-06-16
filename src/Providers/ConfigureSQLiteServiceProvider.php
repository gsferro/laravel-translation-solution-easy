<?php

namespace Gsferro\TranslationSolutionEasy\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class ConfigureSQLiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $dirPckConfig = storage_path('vendor/gsferro/translation-solution-easy/config/sqlite');
        if (is_dir($dirPckConfig)) {
            $this->mergeConfigFrom("{$dirPckConfig}/database.php", 'database.connections');
            $this->mergeConfigFrom("{$dirPckConfig}/translationsolutioneasy.php", 'translationsolutioneasy');
        }
    }

    public function boot() { }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app[ 'config' ]->get($key, []);

        $this->app[ 'config' ]->set($key, mergeConfig(require $path, $config));
    }
}
