# Package Development

## 1. Package Resource

### Requirements

1- Install Laplus:

```shell
compsoer require rapid/laplus
```

2- Install Wrokbench

```shell
composer require --dev "orchestra/testbench"
vendor/bin/testbench workbench:install
```

[Read More...](https://packages.tools/workbench)

### Define Models

To define your models with the help of Laplus, simply add your resource
in your Service Provider:

```php
public function boot()
{
    Laplus::registerPackageResource(
        new PackageResource(
            'rapid/my-package',
            __DIR__ . '/Models',
            __DIR__ . '/../database/migrations',
            __DIR__ . '/../database/migrations/dev_generated',
            __DIR__ . '/../database/travels',
        ),
    );
}
```

By doing this, you are introducing this resource to Laplus.

### Generate Migrations

To generate migrations, you need to run the `dev:migration` and `deploy:migration` commands.
With the help of Workbench, it works as follows:

```shell
php vendor/bin/testbench dev:migration
php vendor/bin/testbench deploy:migration
```

**Note:** The `dev` path defined in packages is excluded from `.gitignore`.


## 2. Shared Package Resource

Shared package resources are essentially resources that are not generated within your package.
Instead, they are generated in the user's project directory.

### Define Models

To define your models with the help of Laplus, simply add your resource
in your Service Provider:

```php
public function boot()
{
    Laplus::registerPackageResource(
        new PackageResource('rapid/my-package', __DIR__ . '/Models', __DIR__ . '/../database/travels'),
    );
}
```

By doing this, you are introducing this resource to Laplus.

Now, when users run the php artisan `dev:migration` or php artisan `deploy:migration` commands,
the migrations for this package will be generated in the `database/migrations/vendor` folder
and will be ready for use.


### Updating the Package

When updating your package, since the user has kept the latest stable version of
the migrations in their project, all changes will be applied simply by running the
`dev:migration` or `deploy:migration` commands.

There is no need to worry about updating the package's table schemas anymore. :)
