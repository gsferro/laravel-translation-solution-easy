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
        
1.  Gerenciamento do locate dentro da app pelo usuário na url

1.  Tradução do banco de dados
    1.  Sessão: Use SQLite, configurando de forma simples com alguns passos rodando 
        - `php artisan gsferro:configure-sqlite` 
    1.  Sessão: Tradução do Banco, Tabelas de configuração traduzidas tanto única quanto múltiplas em `config/translationsolutioneasy`.
        - `php artisan gsferro:translate-tables`
    1.  Traduzir arquivos de langs usando command como é feito no banco    
        - `php artisan gsferro:translate-files`
    
    - `TODO` 
        1.  Informações digitadas pelo usuário 
            - Observer ou listener?
        
    - `TODO LONG TERM`
        - Ao traduzir automaticamente, ter a opção de uma verificação interna (PF / time) e/ou de usuários do sistema Poderem sugerir melhores traduções.
    
### Dependências:

1.  https://github.com/spatie/laravel-translation-loader/tree/2.6.3
1.  https://github.com/mcamara/laravel-localization/

### Versões
| Package | Laravel |
| ------ | ------ |
| ~1.* | 5.8 |
 

### Instalação

```php
composer require gsferro/translation-solution-easy

php artisan vendor:publish --provider="Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider"
php artisan vendor:publish --provider="Gsferro\TranslationSolutionEasy\Providers\TranslationSolutionEasyServiceProvider" --force

```
* Faz `mergeConfigFrom` no ServiceProvider colocando o locale de `app.php` como `pt-br` 
* ###### TODO trocar configuração via command 

### Configurações de uso

1.  Caso não Use SQLite: `php artisan migrate --path=database/migrations/translation` 

1.  No arquivo base de html deve ser colocado:
    ```html
    <html lang="{{ strtolower(str_replace('_', '-', app()->getLocale())) }}">
    ```    
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
    - Ele precisa estar encapsulado pela tag `<ul>`
    - Talvez seja necessário ajustar o flags.css dependendo de onde for colocado 
    
### Use SQLite
1. Command para criar database
    ```php
    php artisan gsferro:configure-sqlite {--database= : Database name}
    ```
- Faz `mergeConfigFrom` no ServiceProvider mesclando a nova config em `config/database` e `config/translationsolutioneasy`
1. Migrate
    ```php
    php artisan gsferro:configure-sqlite-migrate
    ```
- Caso queria deixar de usar o SQLite para usar outro SGBD, excluir arquivos de `vendor/gsferro/translation-solution-easy/config/sqlite`
* ###### TODO remover configuração via command 

### Tradução do Banco

1.  Coloque em `config/translationsolutioneasy.translate-tables` as tabelas com os campos que deseja manter/traduzir
    - ex:
    ```php
    'translate-tables' => [
        'table1' => 'collumn0', 
        // ou
        'table2' => ['collumn1', 'collumn2', ...]
    ],
    ```

1.  Execute o comando via artisan, passe os paramentros caso queira rodar somente em uma única tabela
    ```php 
    php artisan gsferro:translate-tables [--table= : Table name] [--column= : Collumn name]  [--lang= : Language]
    ```
    
### Tradução dos arquivos

1.  Execute o comando via artisan, passe os paramentros caso queira rodar somente em um único arquivo ou lang 
    ```php 
    php artisan gsferro:translate-files [--file= : File name] [--lang= : Language]
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