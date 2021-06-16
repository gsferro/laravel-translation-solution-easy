<?php

namespace Gsferro\TranslationSolutionEasy\Providers;

use Gsferro\TranslationSolutionEasy\Console\Commands\ConfigureSQLiteCommand;
use Gsferro\TranslationSolutionEasy\Console\Commands\TranslationTablesCommand;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

class ConfigureSQLiteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $dirPckConfig = __DIR__.'/../config/temp';
        if (is_dir($dirPckConfig)) {
            /*
            |---------------------------------------------------
            | sobrescreve
            |---------------------------------------------------
            */
            $this->mergeConfigFrom("{$dirPckConfig}/database.php", 'database');
            $this->mergeConfigFrom("{$dirPckConfig}/translationsolutioneasy.php", 'translationsolutioneasy');

            rmdir(__DIR__ . '/../config/temp');
        }
    }

    public function boot() { }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, $this->mergeConfig(require $path, $config));
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
}
