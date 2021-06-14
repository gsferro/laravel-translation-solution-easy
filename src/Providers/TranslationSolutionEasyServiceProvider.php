<?php

namespace Gsferro\TranslationSolutionEasy\Providers;

use Gsferro\TranslationSolutionEasy\Console\Commands\TranslationTablesCommand;
use Gsferro\TranslationSolutionEasy\Services\ReversoTranslation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Illuminate\Routing\Router;

class TranslationSolutionEasyServiceProvider extends ServiceProvider
{
    public function register()
    {
        // registrar command

        // registrar middlewares

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
            ]);
        }

        /*
        |---------------------------------------------------
        | Middleware package mcamara/laravel-localization
        |---------------------------------------------------
        */
        /*$this->app['router']->aliasMiddleware('localize', LaravelLocalizationRoutes::class);
        $this->app['router']->aliasMiddleware('localizationRedirect', LaravelLocalizationRedirectFilter::class);
        $this->app['router']->aliasMiddleware('localeSessionRedirect', LocaleSessionRedirect::class);
        $this->app['router']->aliasMiddleware('localeCookieRedirect', LocaleCookieRedirect::class);
        $this->app['router']->aliasMiddleware('localeViewPath', LaravelLocalizationViewPath::class);*/
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware([
            'localize'                => LaravelLocalizationRoutes::class,
            'localizationRedirect'    => LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect'   => LocaleSessionRedirect::class,
            'localeCookieRedirect'    => LocaleCookieRedirect::class,
            'localeViewPath'          => LaravelLocalizationViewPath::class
        ]);

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

        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations')
        ], 'migrations');

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
            __DIR__ . '/../resouces/lang'  => resource_path('vendor/gsferro/translationsolutioneasy'),
            __DIR__ . '/../resouces/views' => resource_path('vendor/gsferro/translationsolutioneasy'),
        ]);

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/translationsolutioneasy'),
        ]);

        Blade::directive("translationsolutioneasyCss", function(){
            return "<link href='/vendor/gsferro/translationsolutioneasy/css/flags.css' rel='stylesheet' type='text/css'/>";
        });
        Blade::component("vendor.gsferro.translationsolutioneasy.flags", 'translationsolutioneasyFlags');
    }
}