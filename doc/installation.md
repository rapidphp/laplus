
# Laplus Installation
## 1- Install the package with composer:
```shell
composer require rapid/laplus
```

### 2- Publish configs (Optional)
Run this command to publish configs to `config/laplus.php`
```shell
php artisan vendor:publish --tag=laplus
```

### 3- Convert default User model to presentable model (Optional):
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
