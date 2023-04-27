<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'excelreport' => [
            'driver' => 'local',
            'root'  => storage_path('app/generate-report/excelreport'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'unsettled-D1' => [
            'driver' => 'local',
            'root' => storage_path('app/generate-report/unsettled/D1'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'unsettled-D3' => [
            'driver' => 'local',
            'root' => storage_path('app/generate-report/unsettled/D3'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'unsettled-D7' => [
            'driver' => 'local',
            'root' => storage_path('app/generate-report/unsettled/D7'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'settled' => [
            'driver' => 'local',
            'root'  => storage_path('app/generate-report/settled'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        'ftpcpop' => [
            'driver' => 'ftp',
            'host' => '10.11.243.19',
            'username' => 'cardftp',
            'password' => 'cacdftp',
            'passive' => true,
            'timeout' => 30,
            'root' => '/',
            'url' => '/u/cardpro/export/cpop/'
        ],

        'reportcpop' => [
            'driver' => 'ftp',
            'host' => '10.14.19.189',
            'username' => 'tibs',
            'password' => 'tibs',
            'port'  => '21',
            'passive' => true,
            'timeout' => 30,
            'root' => '/',
            'url' => '/MERCTXNR_CSV/'
        ],

        'export' => [
            'driver' => 'local',
            'root' => public_path(),
            'url' => env('APP_URL'),
            'visibility' => 'public',
        ],

        'links' => [
            public_path('storage') => storage_path('app/public'),
        ],

        // 10.14.17.211
        // 21
        // pgis
        // pgis
        // MERCHTXNR_CSV

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
