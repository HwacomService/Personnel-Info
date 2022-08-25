<?php

return [
    //人資shareDB
    'hr'       => [
        'driver'         => 'mysql',
        'host'           => env('HR_DB_HOST', '127.0.0.1'),
        'port'           => env('HR_DB_PORT', '3306'),
        'database'       => env('HR_DB_DATABASE', 'forge'),
        'username'       => env('HR_DB_USERNAME', 'forge'),
        'password'       => env('HR_DB_PASSWORD', ''),
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => false,
        'engine'         => null,
        'options'        => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA     => env('MYSQL_ATTR_SSL_CA'),
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::MYSQL_ATTR_COMPRESS   => true,
        ]) : [],
    ],
];
