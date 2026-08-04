<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Keep the standard Laravel view path available even though this backend
    | is primarily API-first. Some framework commands still bootstrap the
    | view factory and expect these settings to exist.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Use storage_path directly so artisan commands can bootstrap cleanly
    | even before the compiled views directory has been created.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        storage_path('framework/views')
    ),

];
