# Configuration

## Publish

Run the following command to publish configs:

```php
php artisan vendor:publish --tag=laplus
```

This command creates the `config/laplus.php` file

## Model And Migration Discovering

In the `config/laplus.php` we have:

```php
'resources' => [

    'main' => [
        'type' => 'default',
        'migrations' => base_path('database/migrations/auto_generated'),
        'models' => base_path('app/Models'),
        'merge_to_config' => true,
    ],
    
]
```

You can customize the `migrations` and `models` path.

If `merge_to_config` set to true, the `migrations` path will include
    as the migrations path in the laravel framework core

## Modular

If you want to use the package in the modular project,
    add the `modular` resource type:

```php
'modules' => [
    'type' => 'modular',
    'modules' => base_path('Modules'),
    'models' => 'app/Models',
    'migrations' => 'database/migrations/auto_generated',
    'merge_to_config' => true,
],
```

You can customize `modules` path and sub folders as `models` and `migrations`

Finally, Laplus will search the `Modules/*/app/Models`
    and `Modules/*/database/migrations/auto_generated`
