# Laravel Translation Solution Easy

Uma solução robusta e fácil para i18n que contempla as 3 etapas básicas:

1.  Tradução de termos (strings) fixos (views e controllers)

    1. Crie o arquivo `resources/lang/pt-br.json` e coloque os termos fixos, que são usados em todos os lugares, como: 
       - _titulos das paginas_
       - _breadcumb_
       - _menu_
       - _label_ ou _coluna de tabela
       - _frases / textos_
    
       1. Escreva-os como forem ser usados em pt-br:
           - ex: 
           ```json
           {
              "Nome": "Nome",
              "Email": "Email",
              "Nome Social": "Nome Social",
              "Termos de compromisso de uso": "Termos de compromisso de uso"
           }
           ```
       1. Para executar, utilize normalmente o método padrão do laravel:
           - ex:
           ```php
          echo __("Nome Social"); 
          # ou
          {{ __("Termos de compromisso de uso") }}
          ```

    - Crie os arquivos em `resources/lang/pt-br/<arquivo>.php` somente quando o valor for sofrer alteração conforme utilização, faça parte de um componente ou for explícito alguma tradução em especial.
      - ex:
      ```php
        # crud-pessoa.php
        <?php
        
        return [
            "index" => ":attribute recuperados(as) com sucesso",
        ];
        
        // uso
        echo __("crud-pessoa.index", ["attribute" => "Pessoa"]);
        ```
    - Observações relevantes:
        1. #### Acaba sendo inicialmente trabalhoso encapsular todos os textos/termos e incrementar o arquivo json quando o projeto já está em curso
        1. #### Ficará muito mais fácil quando o projeto nasce com essa filosofia
        1. #### Reforce o princípio dentro do time de que em qualquer alteração, encapsular os textos/termos 
        1. #### Não utilize espaço no nome do index dentro dos arquivos php, use o separador `_` ou `-`
        1. #### **ATENÇÃO**: não utilize array bidimensional, a exceção é o validation.php

    1. Para traduzir os arquivos, veja a sessão [Tradução dos arquivos](#tradução-dos-arquivos) de langs usando command como é feito para o banco de dados
        ```bash
        php artisan gsferro:translate-files 
        ```
1.  Gerenciamento do Langs
    1. Veja a sessão: [Configurações de uso](#configuracoes-de-uso) item 1, 2 e 5
    1. O locale ficará como prefixo na url, porém a langs padrão (pt-br), não é exibido 
    1. A gerência é feita de forma automática pelo pacote, você só precisa incluir na view `@translationsolutioneasyFlags()` para o usuário fazer a troca 
    1. O pacote também verifica o browser do usuário para ajudar a setar o locate
    1. Ao fazer chamadas para alguma api que venha a ser feita, colocar no header do http `"accept-language" => "pt-BR,pt;q=0.8",` 
    1. O pacote olha primeiro para a base de dados e depois para o arquivo seguindo o padrão Iterator, nas próximas versões inverteremos, mas nada que irá modificar o seu uso.
    
1.  Tradução do banco de dados
    1.  Caso queira utilizar o Sqlite veja a sessão [Use SQLite](#use-sqlite), no link é visto como configurar de forma simples com alguns passos rodando:
        ```bash
        php artisan gsferro:configure-sqlite
         ```
    1.  Na Sessão [Tradução do Banco de Dados](#tradução-do-banco), é explicado como configurar o pacote para traduzir um ou múltiplas tabelas e colunas no arquivo `config/translationsolutioneasy`:
        ```bash
        php artisan gsferro:translate-tables
        ```
- `TODO` 
    1.  Traduzir automaticamente informações digitadas pelos usuários? 
        - se sim, usar Observer ou listener?
    
    1. Ao traduzir automaticamente, ter a opção de uma verificação interna (PF / time) e/ou dos usuários do sistema poderem sugerir melhores traduções.
    
### Dependências:

1.  https://github.com/spatie/laravel-translation-loader/tree/2.6.3
1.  https://github.com/mcamara/laravel-localization/

### Versões
| Package | Laravel |
| ------ | ------ |
| ~1.* | 5.8 |
 

### Instalação

```bash
composer require gsferro/translation-solution-easy

php artisan vendor:publish --provider="Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider"
php artisan vendor:publish --provider="Gsferro\TranslationSolutionEasy\Providers\TranslationSolutionEasyServiceProvider" --force

```
* Faz `mergeConfigFrom` no ServiceProvider colocando o locale de `app.php` como `pt-br` 
* ###### TODO trocar configuração via command 

### Configurações de uso

1.  No arquivo base de html deve ser colocado:
    ```html
    <html lang="{{ strtolower(str_replace('_', '-', app()->getLocale())) }}">
    ```    
1.  Para o fácil encapsulamento e visando os testes automatizados:
    - Crie o arquivo `routes/withLacale.php`
    ```php
        <?php
        
        if (app()->environment("testing")) {
            require "web.php";
        } else {
            Route::prefix(LaravelLocalization::setLocale())
                ->middleware(["localeSessionRedirect", "localizationRedirect", "localeViewPath"])
                ->group(function () {
                    require "web.php";
                });
        }

    ```
    - Altere `RouteServiceProvider@mapWebRoutes`
    ```php
        Route::middleware("web")
             ->namespace($this->namespace)
             ->group(base_path("routes/withLacale.php"));
    ```
    - Para mais detalhes, veja a sessão [Informações adicionais](#informacoes-adicionais)

1.  Caso não opte por [Use SQLite](#use-sqlite), execute no terminal:
    ```bash
    php artisan migrate --path=database/migrations/translation
    ```

1.  Acesse `config/laravellocalization` e sete quais linguas a sua aplicação irá dar suporte
    - default:
        * `pt-br`
        * `en`
    
1.  Inclua a seleção para troca de idiomas
    - `@translationsolutioneasyFlags()`
    - Ele precisa estar encapsulado pela tag `<ul>`
    - Talvez seja necessário ajustar o `public/vendor/gsferro/translationsolutioneasy/css/flags.css` dependendo de onde for colocado 
    
### Use SQLite
1. Command para criar database
    ```bash
    php artisan gsferro:configure-sqlite {--database= : Database name}
    ```
- Faz `mergeConfigFrom` no ServiceProvider mesclando a nova config em `config/database` e `config/translationsolutioneasy`
1. Migrate
    ```bash
    php artisan gsferro:configure-sqlite-migrate
    ```
- Caso queria deixar de usar o SQLite para usar outro SGBD, excluir arquivos de `vendor/gsferro/translation-solution-easy/config/sqlite`
* ###### TODO remover configuração via command 

### Tradução dos Arquivos

1.  Execute o comando via artisan, passe os paramentros caso queira rodar somente em um único arquivo ou lang
    ```bash
    php artisan gsferro:translate-files [--file= : File name] [--lang= : Language] [--force :  : default false, execute translate if no exists]
    ```

### Tradução do Banco de Dados

1. Coloque em `config/translationsolutioneasy.translate-tables` as tabelas com o(s) campo(s) que deseja traduzir:
    - ex:
    ```php
        "translate-tables" => [
            "paises" => "nome", 
            // ou
            "servicos" => [
                "nome",
                "descricao",
                "observacoes",
            ]
        ],
    ```
    * Obs: Coloque somente as colunas que fazem sentido serem traduzidas. Colunas contendo datas e numero não fazem.
    
1.  Nas Models:
    1. Coloque a interface `TranslationColumnsInterface`     
    1. Coloque a Trait `TranslationColumnsTrait`
    1. Caso não tenha configurado o item 1 (inline), sete o atributo `public $translationColumns = ["<name-column1>", ...]` 
    
1.  Execute o comando via artisan, passe os paramentos caso queira rodar somente numa única tabela/coluna (inline)
    ```bash
    php artisan gsferro:translate-tables [--table= : Table name] [--column= : Collumn name]  [--lang= : Language] [--force :  : default false, execute translate if no exists]
    ```

- `TODO`
    - Ter a opção de passar a model e pegar as informações direto dela, assim é viável fazer traduções em multiplas conexões de banco de dados.
    
### Informações Adicionais
* Option `--force`: 
  - Add na v1.3.0, por default é false, ou seja, só ira buscar uma tradução caso não exista para a lingua escolhida, diminuindo substancialmente o tempo; 
  - Caso coloque como true, executara para todos, pode demorar horas dependendo do tamanho do arquivo ou tabela.  
* O limite para cada tradução é de 3k caracteres
* Testing:
    - https://github.com/ARCANEDEV/Localization/issues/113 (working)
    - https://github.com/mcamara/laravel-localization#testing (not working)
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