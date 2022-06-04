<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => env('MYSQL_QUEUE_RETRY_AFTER_SECONDS', 1800),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => env('REDIS_QUEUE_RETRY_AFTER_SECONDS', 240),
            'block_for' => null,
        ],

        'redis-long-running' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_LONG_QUEUE', 'default_long'),
            'retry_after' => env('REDIS_LONG_QUEUE_RETRY_AFTER_SECONDS', 3600),
            'block_for' => null,
        ],

        'rabbitmq-parser-wb' => [
            'queue' => env('RABBITMQ_PARSER_WB_QUEUE', 'default'),
            'host' => env('RABBITMQ_PARSER_HOST', '127.0.0.1'),
            'port' => env('RABBITMQ_PARSER_PORT', 5672),
            'user' => env('RABBITMQ_PARSER_USER', 'guest'),
            'password' => env('RABBITMQ_PARSER_PASSWORD', 'guest'),
            'vhost' => env('RABBITMQ_PARSER_VHOST', '/'),
        ],

        'rabbitmq-parser-oz' => [
            'queue' => env('RABBITMQ_PARSER_OZ_QUEUE', 'default'),
            'host' => env('RABBITMQ_PARSER_HOST', '127.0.0.1'),
            'port' => env('RABBITMQ_PARSER_PORT', 5672),
            'user' => env('RABBITMQ_PARSER_USER', 'guest'),
            'password' => env('RABBITMQ_PARSER_PASSWORD', 'guest'),
            'vhost' => env('RABBITMQ_PARSER_VHOST', '/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
