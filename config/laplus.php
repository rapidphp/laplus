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
            'models' => base_path('app/Models'),
            'merge_to_config' => true,
        ],

        // 'modules' => [
        //     'type' => 'modular',
        //     'modules' => base_path('Modules'),
        //     'models' => 'app/Models',
        //     'migrations' => 'database/migrations/deploy',
        //     'merge_to_config' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Dev Path
    |--------------------------------------------------------------------------
    |
    | Dev path is a path with git ignore to test the application in the local
    | environment.
    |
    */
    'dev' => [
        'migrations' => base_path('database/migrations/dev_generated'),
        'git_ignore' => true,
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
        | Build On Generate Command
        |--------------------------------------------------------------------------
        |
        | If set this value to true, the `guide+` command will run after each
        | generating commands like `generate+` and `regenerate+`.
        |
        */
        'on_generate' => false,

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
