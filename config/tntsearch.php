<?php

return [
    'storage' => storage_path('search') . DIRECTORY_SEPARATOR,

    'driver'    => env('DB_CONNECTION', 'mysql'),
    'host'      => env('DB_HOST', '127.0.0.1'),
    'database'  => env('DB_DATABASE'),
    'username'  => env('DB_USERNAME'),
    'password'  => env('DB_PASSWORD'),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',

    'stemmer'   => \TeamTNT\TNTSearch\Stemmer\NoStemmer::class,

    'wal' => false,
];
