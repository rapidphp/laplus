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
    */
    'resources' => [

        'main' => [
            'migrations' => base_path('database/migrations/auto_generated'),
            'models' => base_path('app/Models'),
            'merge_to_config' => true,
        ],

    ],

];
