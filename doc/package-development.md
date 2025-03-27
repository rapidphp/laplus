# Package Development

### Define Models

To define your models with the help of Laplus, simply add your resource
in your Service Provider:

```php
public function boot()
{
    Laplus::registerPackageResource(
        new PackageResource('rapid/my-package', __DIR__ . '/Models'),
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