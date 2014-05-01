<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings Table
    |--------------------------------------------------------------------------
    |
    | The database table used to store settings. You may change this if it
    | conflicts with an existing table in your application.
    |
    */

    'table' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings are cached as a single collection to minimise database queries.
    | You may disable caching, change the cache key, or adjust the TTL (in
    | seconds). Setting ttl to null caches indefinitely.
    |
    */

    'cache' => [
        'enabled' => true,
        'key' => 'app_settings',
        'ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Fallback values returned by Settings::get() when a key does not exist
    | in the database. These are never persisted to the database.
    |
    | Example:
    |   'defaults' => [
    |       'app.timezone'     => 'UTC',
    |       'mail.from_name'   => 'My App',
    |   ],
    |
    */

    'defaults' => [],

];
