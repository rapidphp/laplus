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
- [Guide Generator](doc/guide)


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

[Read more...](doc/present.md#make-presentable-model)


## Make model with inline present
You can use this command to create a model with inline present:
```shell
php artisan make:model+ Name --inline
```

This command will create `app/Models/Name.php` model.

[Read more...](doc/present.md#inline-present)


## Migrations

### Generate Migrations
Run this command to automatically found the updates and generate migrations:
```shell
php artisan generate+
```

[Read more...](doc/migration.md#generate)

### Regenerate Migrations
This command is same with `generate+`, but the difference is clearing the migration folder!
```shell
php artisan regenerate+
```
> Warning: This command will remove all your migration files (exclude files starts with `0001_01_01` name)

[Read more...](doc/migration.md#regenerate)

### Generate And Migrate
Following command, run `generate+` and `migrate` at once:
```shell
php artisan migrate+
```

[Read more...](doc/migration.md#generate-and-migrate)


## Label Translator

Present the model labels:

```php
class UserLabelTranslator extends LabelTranslator
{
    public function gender(bool $emoji = false)
    {
        return $this->value?->toString($emoji); // Returns gender name or null
    }
}
```

And use it easily:

```html
<p>{{ $user->gender_label }}</p>
<p>{{ $user->gender_label(emoji: true) }}</p>
```

> Labels always return a string value. If the value is null, it returns `"Undefined"`.

[Read more...](doc/label.md)


## Guide Generator

Guide automatically generate the model docblock using the
columns, attributes and relationships:

```php
class User extends Model
{
    use HasPresent;
    use HasLabels;
    
    protected function present(Present $present)
    {
        $present->id();
        $present->string('name');
    }
    
    #[IsRelation]
    public function avatars()
    {
        return $this->hasMany(Avatar::class);
    }
    
    public function getFirstName() : string
    {
        return Str::before($this->name, ' ');
    }
}
```

It generates:

```php
/**
 * @Guide
 * @property int $id
 * @property string $name
 * @property Collection<Avatar> $avatars
 * @property string $name_label
 * @property string name_label()
 * @property string $first_name
 * @EndGuide
 */
class User extends Model
```

[Read more...](doc/guide.md)


## More Document
**More document found in [Documents](#documents) section.**
