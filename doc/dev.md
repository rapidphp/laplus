# Development Utils

After making any desired changes to the presentations, run the following command once:

```shell
php artisan dev:migrate --guide
```

Or if you want to fresh the database once:

```shell
php artisan dev:migrate --fresh --guide
```

## Migrations

Generate new migrations with the following command:

```shell
php artisan dev:migration
```

These migrations are generated in a folder that is ignored by git.
This means that you can generate migrations continuously during development and debugging
without worrying about their large number or sloppiness.

With the following command, all dev migrations will be deleted and regenerated.
This can help in your development process.

```shell
php artisan dev:migration --fresh
```

Remove the migrations created in dev with the following command:

```shell
php artisan dev:migration --clear
```

## Guide

You can generate guide documents with the following command:

```shell
php artisan dev:guide
```

[Read More...](guide.md)


## Deploy To Production

[Read More...](deploy.md)
