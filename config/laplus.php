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
            'migrations' => base_path('database/migrations/deploy'),
            'dev_migrations' => base_path('database/migrations/dev_generated'),
            'models' => base_path('app/Models'),
            'merge_to_config' => true,
        ],

        // 'modules' => [
        //     'type' => 'modular',
        //     'modules' => base_path('Modules'),
        //     'models' => 'app/Models',
        //     'migrations' => 'database/migrations/deploy',
        //     'dev_migrations' => 'database/migrations/dev_generated',
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

];
