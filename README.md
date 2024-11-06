# Laplus
Laravel plus+ add presentation for your models

```php
public function present()
{
    $this->id();
    $this->text('title');
    $this->string('password')->cast('hashed')->hidden();
    $this->belongsTo(Artist::class)->cascadeOnDelete();
}
```

Write your presents in long time:

![Write presents in long time, laplus will create your migrations](doc/how_works_1.png)

Laplus will generate your migrations:

![Laplus will create your migrations](doc/how_works_2.png)


## Requirements
* Php 8.2 or higher
* Laravel 11.0


## Documents
- [Installation](doc/installation.md)
- [Configuration](doc/configuration.md)
- [Present](doc/present.md)
- [Migration](doc/migration.md)
- [Label](doc/label.md)
- [Docblock Generator](doc/docblock.md)


## Installation
### 1- Install the package with composer:
```shell
composer require rapid/laplus
```

### 2- Publish configs
Run this command to publish configs to `config/laplus.php`
```shell
php artisan vendor:publish --tag=laplus
```

### 3- Convert default User model to presentable model (optional):
+ Add `HasPresent` trait:
```php
class User extends Model
{
    use HasPresent;
}
```

+ Remove `$fillable`, `$hidden` and `casts()` values:
```php
//protected $fillable = ['name', 'email', 'password'];
//protected $hidden = ['password', 'remember_token'];
//protected function casts() { ... }
```
> Laplus will automatically add this values.

+ Create `UserPresent` class with the following command:
```shell
php artisan make:user-present
```
Or add below code in User class:
```php
protected function present(Present $present)
{
    $present->id();
    $present->string('name');
    $present->string('email')->unique();
    $present->timestamp('email_verified_at')->nullable();
    $present->password();
    $present->rememberToken();
    $present->timestamps();
}
```

+ Move default migration to laplus path:

Find `database/migrations/0001_01_01_000000_create_users_table.php` file and move it
    into `database/migrations/auto_generated` folder (create it if doesn't exists)


## Make model & present
You can use this command to create a model and a present:
```shell
php artisan make:model+ Name
```

This command will create `app/Models/Name.php` model and `app/Presents/NamePresent.php` present.


## Make model with inline present
You can use this command to create a model with inline present:
```shell
php artisan make:model+ Name --inline
```

This command will create `app/Models/Name.php` model.



## Migrations

### Generate Migrations
Run this command to automatically found the updates and generate migrations:
```shell
php artisan generate+
```

### Regenerate Migrations
This command is same with `generate+`, but the difference is clearing the migration folder!
```shell
php artisan regenerate+
```
> Warning: This command will remove all your migration files (exclude files starts with `0001_01_01` name)

### Generate And Migrate
Following command, run `generate+` and `migrate` at once:
```shell
php artisan migrate+
```

## More Document
More document found in [Documents](#documents) section.
