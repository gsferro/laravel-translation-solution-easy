<?php

namespace Gsferro\TranslationSolutionEasy\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Arr;

class ConfigureSQLiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $dirPckConfig = storage_path('app/config-sqlite');
        dump($dirPckConfig);
        if (is_dir($dirPckConfig)) {
            dump(1);
            /*
            |---------------------------------------------------
            | sobrescreve
            |---------------------------------------------------
            */
            $this->mergeConfigFrom("{$dirPckConfig}/database.php", 'database');
            $this->mergeConfigFrom("{$dirPckConfig}/translationsolutioneasy.php", 'translationsolutioneasy');

            //            Storage::delete(['config-sqlite/database.php', 'config-sqlite/translationsolutioneasy.php']);

            $this->delTree($dirPckConfig);
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


    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
