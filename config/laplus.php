<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laplus Resources
    |--------------------------------------------------------------------------
    |
    | This values is list of resources that laplus should search and optimize.
    | Using `php artisan laplus:generate` to generate from these path.
    | Add `'merge_to_config' => true` to merge migrations path, to configs.
    |
    | Allowed types: default, modular
    |
    */
    'resources' => [

        'main' => [
            'type' => 'default',
            'models' => base_path('app/Models'),
            'migrations' => database_path('migrations/deploy'),
            'dev_migrations' => database_path('migrations/dev_generated'),
            'travels' => database_path('travels'),
            'merge_to_config' => true,
        ],

        // 'modules' => [
        //     'type' => 'modular',
        //     'models' => 'app/Models',
        //     'modules' => base_path('Modules'),
        //     'migrations' => 'database/migrations/deploy',
        //     'dev_migrations' => 'database/migrations/dev_generated',
        //     'travels' => 'database/travels',
        //     'merge_to_config' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Laplus Guide
    |--------------------------------------------------------------------------
    |
    | Laplus guide is a technology to generate automatically the model docblock
    |
    */
    'guide' => [

        /*
        |--------------------------------------------------------------------------
        | Guide Type
        |--------------------------------------------------------------------------
        |
        | Allowed types: docblock, mixin
        |
        | - docblock: Generate docblock in the model file
        | - mixin: Generate docblock in the cache, and add @mixin in the model file
        |
        */
        'type' => 'mixin',

        /*
        |--------------------------------------------------------------------------
        | Mixin Guide Settings
        |--------------------------------------------------------------------------
        |
        | Customize the mixin namespace and caching path
        |
        */
        'mixin' => [
            'namespace' => 'Rapid\_Stub',
            'path' => storage_path('stubs/rapid/guide.stub.php'),
            'git_ignore' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Packages Settings
    |--------------------------------------------------------------------------
    |
    | Settings for vendor packages that contains models
    |
    */
    'vendor' => [
        'migrations' => database_path('migrations/vendor'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Structure settings
    |--------------------------------------------------------------------------
    |
    | Columns, Indexes and other structures that migrate the database, in
    | global scope.
    |
    | map -> Map the column types in Present to blueprint method.
    |
    */
    'structures' => [
        'map' => [
            // 'id' => 'uuid',
        ],
    ],

];
