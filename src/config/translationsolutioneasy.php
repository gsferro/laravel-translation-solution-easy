<?php

return [

    /*
    |---------------------------------------------------
    | Table names with their respective columns
    |---------------------------------------------------
    |
    | Structure exemple:
    |   'translate-tables' => [
    |       'name' => 'collumn', ||  'name' => ['collumn1', 'collumn2', ...]
    |   ],
    |
    */

    'translate-tables' => [],

    /*
    |---------------------------------------------------
    | Connection different
    |---------------------------------------------------
    |
    | If you want to use another database to save the
    | translations, change here.
    |
    | If you use SQLite, this package with commond line
    | for implement new config in database and this config.
    | The new settings will be in storage/vendor/gsferro/translation-solution-easy/config/sqlite
    | and model TranslationSolutionEasy will connect to it ignoring or that for placed here.
    |
    | Default: false
    |
    */

    'connection' => false,
];