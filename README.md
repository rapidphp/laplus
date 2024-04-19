# Laplus
Laravel plus+ add presentation for your models!

```php
public function present()
{
    $this->id();
    $this->text('title');
    $this->string('password')->cast('hashed')->hidden();
    $this->belongsTo(Artist::class)->cascadeOnDelete();
}
```

Then automatically create migrations.


## Requirements
* Php 8.2 or higher
* Laravel 11.0


## Installation
### 1- Install the package with composer:
```shell
composer require rapid/laplus
```

### 2- Convert default User model to presentable model:
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


## Make model & present
You can use this command to create a model and a present:
```shell
php artisan make:model-laplus Name
```

This command will create `app/Models/Name.php` model and `app/Presents/NamePresent.php` present.


## Customize model present
- If you want to set the target present using class name, you can override method `getPresentClass`:
```php
class User extends Model
{
    public function getPresentClass()
    {
        return MyCustomUserPresent::class;
    }
}
```

- If you want to set the target present using object instance, you can override method `getPresent`:
```php
class User extends Model
{
    public function getPresent()
    {
        return new MyCustomUserPresent($this);
    }
}
```
> Note: This value will be cached!

- If you want to present the model inline, add the `present` method in the model:
```php
class User extends Model
{
    public function present(Present $present)
    {
        $present->id();
        $present->timestamps();
    }
}
```


## Present the model
### Present column
You can add columns using `$this` reference like Blueprint:
```php
class MoviePresent extends Present
{
    public function present()
    {
        $this->id();
        $this->text('name');
        $this->string('icon')->nullable();
        $this->timestamps();
    }
}
```

### Present attribute
Add attribute with callback to define custom values:

```php
$this->attribute('is_artist', get: fn($model) => !is_null($model->artist_id));
```

### Present relationship
Add `belongsTo` to define foreign key and relationship. And `HasOne`, `HasMany` and ... to define relationships.
```php
class PostPresent extends Present
{
    public function present()
    {
        $this->belongsTo(User::class, 'author', 'author_id');
        $this->belongsToMany(Category::class, 'categories');
    }
}

class UserPresent extends Present
{
    public function present()
    {
        $this->hasMany(Post::class, foreignKey: 'author_id'); // name: 'posts'
    }
}
```

### Fillable & Hidden
You can set fillable or not-fillable a column:
```php
$this->text('name')->fillable(); // Default is enabled
$this->text('icon')->fillable(false);
```

Or hidden a column:
```php
$this->string('password')->cast('hashed')->hidden();
```

### Casting
If you are using laplus methods like `$this->int()` and ..., castings are automatically set.
```php
$this->datetime('modify_at'); // Cast = 'datatime'
```

Set casting type:
```php
$this->string('password')->cast('hashed');
$this->string('true_false')->cast('boolean');
$this->string('test')->cast(TestCastClass::class);
$this->datetime('modify_at')->cast(null);
```

Custom casting:
```php
$this->text('days')->castUsing(
    get: fn ($value) => +substr($value, 0, -5),
    set: fn ($value) => "$value Days",
);
// Now $this->days = 20, saves "20 Days"
```

### Files
File column:
```php
$this->file('image')->disk('images');
```

Now you can work with the file like this:
```php
$url = $model->file('image')->url();
$path = $model->file('image')->path();
$model->file('image')->delete();
```

#### Customize url & download:
Use `url` and `urlRoute` methods in present:
```php
$this->file('image')->disk('images')->urlRoute('download');
```
And define the route and controller:
```php
Route::get('download/{model}', [DownloadController::class, 'download'])->name('download');
```
```php
class DownloadController extends Controller
{
    public function download(Model $model)
    {
        return $model->file('image')->download();
    }
}
```

#### Get disk statically:
Use `attr` method:
```php
Storage::disk(Model::attr('image', 'disk'))->put('new-file.png', $file);
```

