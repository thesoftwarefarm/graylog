<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Host Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your project. This value is used to
    | differentiate between multiple projects which push graylog
    | messages to the same graylog server.
    |
    | Example: "my_project"
    |
    */

    'host' => '',

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This value is used to determine which database connection to use. Use a
    | valid connection which is defined in config/database.php
    |
    */

    'database_connection' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Graylog server
    |--------------------------------------------------------------------------
    |
    | IP and port for Graylog server. These are required if this project is
    | configured to send messages to graylog server after they were inserted
    | into database
    |
    */

    'ip' => env('GRAYLOG_IP', null),
    'port' => env('GRAYLOG_PORT', null),

    /*
    |--------------------------------------------------------------------------
    | Max retries
    |--------------------------------------------------------------------------
    |
    | Configure here the max number of attempts in case of a failure when
    | pushing to graylog server.
    |
    */

    'max_retries' => 10,
];