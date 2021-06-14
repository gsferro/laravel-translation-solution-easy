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

class TranslationSolutionEasyServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
        |---------------------------------------------------
        | sobrescreve
        |---------------------------------------------------
        */
        $this->mergeConfigFrom(__DIR__.'/../config/config-basic.php', 'app');
    }

    public function boot()
    {
        /*
        |---------------------------------------------------
        | setando os serviços
        |---------------------------------------------------
        */
        app()->bind('reversotranslation', function ($langFrom, $langTo) {
            return new ReversoTranslation($langFrom, $langTo);
        });

        /*
        |---------------------------------------------------
        | command
        |---------------------------------------------------
        */
        if ($this->app->runningInConsole()) {
            // publicando os demais pacotes
            $this->commands([
                TranslationTablesCommand::class,
                ConfigureSQLiteCommand::class,
            ]);
        }

        /*
        |---------------------------------------------------
        | Middleware package mcamara/laravel-localization
        |---------------------------------------------------
        */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('localize', LaravelLocalizationRoutes::class);
        $router->aliasMiddleware('localizationRedirect', LaravelLocalizationRedirectFilter::class);
        $router->aliasMiddleware('localeSessionRedirect', LocaleSessionRedirect::class);
        $router->aliasMiddleware('localeCookieRedirect', LocaleCookieRedirect::class);
        $router->aliasMiddleware('localeViewPath', LaravelLocalizationViewPath::class);

        /*
        |---------------------------------------------------
        | Publish
        |---------------------------------------------------
        */
        $this->publishes([
            __DIR__ . '/../config/translationsolutioneasy.php' => config_path('translationsolutioneasy.php'),
            __DIR__ . '/../config/laravellocalization.php'     => config_path('laravellocalization.php'),
            __DIR__ . '/../config/translation-loader.php'      => config_path('translation-loader.php'),
        ], 'config');

        if (! class_exists('CreateLanguageLinesTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../migrations/create_language_lines_table.php.stub' => database_path('migrations/translation/'.$timestamp.'_create_language_lines_table.php'),
            ], 'migrations');
        }

        // flags
        $this->loadTranslationsFrom(__DIR__.'/../resouces/lang', 'gsferro/translationsolutioneasy/lang');
        $this->loadTranslationsFrom(__DIR__.'/../resouces/views', 'gsferro/translationsolutioneasy/flags');
        $this->publishes([
            __DIR__ . '/../resouces/lang'  => resource_path('lang/vendor/gsferro/translationsolutioneasy/lang'),
            __DIR__ . '/../resouces/views' => resource_path('views/vendor/gsferro/translationsolutioneasy/views/flags'),
        ]);

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/gsferro/translationsolutioneasy'),
        ]);

        Blade::directive("translationsolutioneasyCss", function(){
            return "<link href='/vendor/gsferro/translationsolutioneasy/css/flags.css' rel='stylesheet' type='text/css'/>";
        });
        Blade::include("vendor.gsferro.translationsolutioneasy.views.flags.flags", 'translationsolutioneasyFlags');

    }

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
