<?php

return [
    ':database-sqlite' => [
        'driver'                  => 'sqlite',
        'database'                => database_path( ':database-sqlite.sqlite' ),
        'prefix'                  => '',
        'foreign_key_constraints' => env( 'DB_FOREIGN_KEYS', true ),
    ]
];