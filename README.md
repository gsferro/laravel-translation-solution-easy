# Laravel Translation Solution Easy

Uma solução completa para i18n que contempla as 3 etapas básicas:


1.  Tradução de strings fixos (views e controllers)

    1. Para tudo que for estatico: botoes, label, validations e etc
    metodo padrão do laravel
    ex:
        `__('botao.salvar')`
    1.  para termos fixos, como titulo, nome de label ou coluna ficara em arquivo <lang>.json

    * obs : O mais trabalhoso quando o projeto já esta em curso
    * obs2: O mais fácil quando o projeto nasce com essa filosofia / RNF
        
1.  Gerenciamento do locate dentro da app pelo usuário

1.  Tradução do banco de dados
    1.  Tbls de configuração
        - criação de um command para listar as tbls e salvar as traduções em banco
    
    - `TODO` 
    1.  Informações digitadas pelo usuário 
        - Observer ou listener?
        
    - `TODO LONG TERM`
        - Ao traduzir automaticamente, ter a opção de uma verificação interna (PF / time) e/ou de usuários do sistema
        Poderem sugerir melhores traduções.
    
### Dependências:

1.  https://github.com/spatie/laravel-translation-loader/tree/2.6.3
1.  https://github.com/mcamara/laravel-localization/

### Instalação

```php
composer require gsferro/translation-solution-easy

php artisan vendor:publish --provider="Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider"
php artisan vendor:publish --provider="Gsferro\TranslationSolutionEasy\Providers\TranslationSolutionEasyServiceProvider"  --force

php artisan migrate [ --database=sqlite ] --path=database/migrations/translation
```

### Usando SQLite
1. Criar arquivo `database/database.sqlite`
    - `touch database/database.sqlite`
    - Se atentar ao nome usado dentro de `config/database.php` caso queira mudar
1. Adicione em `config/translationsolutioneasy`
    - Connection => 'sqlite';

### Configurações
1.  No arquivo base de html deve ser colocado assim:
```html
<html lang="{{ strtolower(str_replace('_', '-', app()->getLocale())) }}">
```    
1.  Alterar dentro de `config/app.php`
    -'locale' => 'pt-br',
    - fallback_locale => 'pt-br', 
    - faker_locale => 'pt-br', 
    - 'providers'
       - comentar a linha
           - `Illuminate\Translation\TranslationServiceProvider::class,`
       - add a linha
           - `Spatie\TranslationLoader\TranslationServiceProvider::class,`
1.  Acesse `config/laravellocalization` e sete quais linguas sua app irá dar suporte

1.  Encapsule as rotas em `web.php` ou `RouteServiceProvider@mapWebRoutes`
    - `web.php`
    ```php
    Route::prefix(LaravelLocalization::setLocale())
        ->middleware([ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ])
        ->group(function() {
       // suas rotas
    });
    ```
    - `RouteServiceProvider@mapWebRoutes`
    ```php
    Route::middleware('web')
         ->namespace($this->namespace)
         ->group(function (){
            Route::prefix(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale())
             ->middleware([ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ])
             ->group(base_path('routes/web.php'));         
    });
    ```

1.  Inclua a seleção para troca de idiomas
    - `@translationsolutioneasyFlags()`
    - ele precisa estar encapsulado pela tag ul
    - talvez seja necessário ajustar o flags.css dependendo de onde for colocado 
    
### Tradução do Banco

1.  Coloque em `config/translationsolutioneasy.translate-tables` as tabelas com os campos que deseja traduzir
    - ex:
    ```php
    'translate-tables' => [
        'table1' => 'collumn0', 
        'table2' => ['collumn1', 'collumn2', ...]
    ],
    ```

1.  Instale e execute o comando via artisan, passe os paramentros caso queira rodar somente em uma unica tabela
    ```php 
    php artisan list | grep gsferro
    
    php artisan gsferro:translate-tables [table= : Table name] [column= : Collumn name]
    ```

### Informações adicionais
* Cache das rotas:
    - https://github.com/mcamara/laravel-localization#caching-routes
* Post not work:
    - https://github.com/mcamara/laravel-localization#post-is-not-working    
* Traduzir rotas:    
    - https://github.com/mcamara/laravel-localization#translated-routes

### Credits

* [Reverso Tradução](https://www.reverso.net/)
* [Freek Van der Herten](https://github.com/freekmurze)
* [Marc Cámara](https://github.com/mcamara)

### License
Laravel Localization is an open-sourced laravel package licensed under the MIT license