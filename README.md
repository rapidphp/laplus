# Laplus ➕
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

## What's Changed In V4

- Command names changed
- New development & production deploy tools
- Removed slug and file columns
- Supports renames and changes a column at the same time
- Supports package resources
- Added travels ([read more...](doc/travel.md))
- Added validation generators ([read more...](doc/validation.md))

## Features

### 1. Migration Generating

Define the structure of your model: columns, indexes, data types, enums, relationships,
or even extensibility with traits.

```php
class Blog extends Model
{
    use HasPresent;
    use HasSlug; // Will extend the 'slug' column

    public function present(Present $present)
    {
        $present->id();
        $present->string('title');
        $present->belongsTo(Category::class);
        $present->enum('status', Status::class);
        $present->yield();
        $present->timestamps();
    }
}
```

Don't get involved in migrations! Because Laplus has taken responsibility for all migrations,
and it builds all migrations by reading your presentations 😎

[Read more...](doc/dev.md)


### 2. Auto Fills

No need to redefine \$fillable, \$cast and $hidden!
Defining it in the present specifies everything. So Laplus will autofill these variables. 😉

```php
// These values will automatically fills:
// protected $fillable = ['id', 'slug'];
// protected $cast = ['password' => 'hashed'];
// protected $hidden = ['password'];
```

[Read more...](doc/present.md#fillable)


### 3. IDE Friendly

When all the columns, their types, and their relationships are known,
why shouldn't the IDE show them to us?

Laplus introduces all of this to the IDE by
generating a neat (and out-of-model) document. 📝

```php
/**
 * @property int $id
 * @property string $name
 * @property \App\Enums\Gender $gender
 * @method BelongsTo<Profile> profile()
 * @property Profile $profile
 */
```

[Read more...](doc/guide.md)


### 4. Travels

Once a version of the project has been deployed, it is almost impossible to change the database.

Travels allow you to make changes to the table records as well as change the table structure. ✈️

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public string|array $whenAdded = 'full_name';
    public string|array $prepareNullable = 'full_name';

    public function up(): void
    {
        User::all()->each(function (User $user) {
            $user->update([
                'full_name' => $user->first_name . ' ' . $user->last_name,
            ]);
        });
    }
};
```

In the above example, we are going to remove `first_name` and `last_name` from the users table
and replace them with `full_name`.

The above class is responsible for generating the `full_name` value using the previous
data before deleting `first_name` and `last_name`.

[Read more...](doc/travel.md)


## Requirements
* Php 8.2 or higher
* Laravel 11.0


## Documents
- [Installation](doc/installation.md)
- [Configuration](doc/configuration.md)
- [Present](doc/present.md)
- [Dev Utils](doc/dev.md)
- [Deploy](doc/deploy.md)
- [Label](doc/label.md)
- [Guide](doc/guide.md)
- [Travel](doc/travel.md)
- [Package Development](doc/package-development.md)
- [Validation Generator](doc/validation.md)


## Installation
### 1- Install the package with composer:
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

Add below code in User class:
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
    into `database/migrations/deploy` folder (create it if doesn't exists)

## Development Utils

To be able to update your tables during development, you can use the following command:

```shell
php artisan dev:migrate --guide
```

[Read more...](doc/dev.md)


## Deployment

When deploying the project to production, you can create the final migrations with the following command:

```shell
php artisan deploy:migrate
```

> For greater project safety, you can follow the scenario we have written on the page below.
> 
[Read more...](doc/deploy.md)

## Make model
You can use this command to create a model and a present:
```shell
php artisan make:model-laplus Name
```

This command will create `app/Models/Name.php` with inline presentation.

[Read more...](doc/present.md#make-presentable-model)


## Make model with separately present
You can use this command to create a model with present class:
```shell
php artisan make:model-laplus --present Name
```

This command will create `app/Models/Name.php` model and `app/Presents/NamePresent.php` present.

[Read more...](doc/present.md#separately-present)


## Migrations

### Generate Migrations
Run this command to automatically found the updates and generate migrations:
```shell
php artisan dev:migration
```

[Read more...](doc/dev.md)

### Update Database
Following command, run `dev:migration` and `migrate` at once:
```shell
php artisan dev:migrate
```

[Read more...](doc/dev)


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
 * @property Collection<int, Avatar> $avatars
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
