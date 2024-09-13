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
            'migrations' => base_path('database/migrations/auto_generated'),
            'models' => base_path('app/Models'),
            'merge_to_config' => true,
        ],

        // 'modules' => [
        //     'type' => 'modular',
        //     'modules' => base_path('Modules'),
        //     'models' => 'app/Models',
        //     'migrations' => 'database/migrations/auto_generated',
        //     'merge_to_config' => true,
        // ]

    ],

];
