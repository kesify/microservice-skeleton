<?php

return [
    'organization_connection' => [
        'driver' => 'mysql',
        'host' => env('ORGANIZATION_DB_HOST', '127.0.0.1'),
        'port' => env('ORGANIZATION_DB_PORT', '3306'),
        'database' => env('ORGANIZATION_DB_NAME', 'organization_db'),
        'username' => env('ORGANIZATION_DB_USERNAME', 'root'),
        'password' => env('ORGANIZATION_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
];
