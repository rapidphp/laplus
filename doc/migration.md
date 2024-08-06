# Laplus Migrations

## Generate
Run this command to automatically found the updates and generate migrations:
```shell
php artisan generate+
```
> If you want to customize output migration names, you can rename it!

### Concept
How generate works?

1- First, Laplus searchs migrations folder (configured in `config/laplus.php`)
and load all migration structures.

2- Then, Laplus asks all models (configured in `config/laplus.php`) to present their self.

3- Then, Laplus tries to find what columns has been added, deleted, modified, or renamed.

4- Finally, generates new migration files.

## Regenerate
This command is same with `generate+`, but the difference is clearing the migration folder!
```shell
php artisan regenerate+
```
> Warning: This command will remove all your migration files (exclude files starts with `0001_01_01` name and snapshot guarded)

## Generate And Migrate
Following command, run `generate+` and `migrate` at once:
```shell
php artisan migrate+
```

## Snapshot
Take new snapshot using following command:
```shell
php artisan snapshot+
```

Snapshots is marking the production of your application. When you have the new stable version, call the top command.

Now, when you use `rgenerate+`, all the migrations removed from last snapshot. That means every migrations in the
    previous version will be safe.
