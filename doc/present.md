# Laplus Present
Presents are defining model columns and attributes, in the Present file.
Presents can be generating $fillable, $casts, and migrations!

## Make Presentable Model
Using following command:
```shell
php artisan make:model+ Name
```

This command will create `app/Models/Name.php` model and `app/Presents/NamePresent.php` present.


### Inline Present
Add `--inline` or `-i` in the command:
```php
php artisan make:model+ Name --inline
```

This command will create `app/Models/Name.php` model.


## Manually Create Present
1- Add `HasPresent` to the model:
```php
class Movie extends Model
{
    use HasPresent;
}
```

2- Create a class in the `app/Presents` extending `Present`:
```php
class MoviePresent extends Present
{
    public function present()
    {
        $this->id();
        $this->timestamps();
    }
}
```
- Note: Class name should starts with model name and ends with `Present` word (it's default detection).

For example if you have `app/Models/Movie`, your present should be `app/Presents/MoviePresent`.

And if you have `app/Models/Shop/Product`, your present should be `app/Presents/Shop/ProductPresent`.

Default detection replace the `Models` directory in the path to `Presents` (doesn't matter what folder you're in).


## Manually Create Inline Present
1- Add `HasPresent` to the model:
```php
class Movie extends Model
{
    use HasPresent;
}
```

2- Add `present` method in the model:
```php
class Movie extends Model
{
    use HasPresent;
    
    protected present(Present $present)
    {
        $present->id();
        $present->timestamps();
    }
}
```

## Customize Present Path
### Set The Class
If you want to set the target present using class name, you can override method `getPresentClass`:
```php
class User extends Model
{
    public function getPresentClass()
    {
        return MyCustomUserPresent::class;
    }
}
```

### Set The Object
If you want to set the target present using object instance, you can override method `makePresent`:
```php
class User extends Model
{
    public function makePresent()
    {
        return new MyCustomUserPresent($this);
    }
}
```
> Note: This value will be cached!

### Set Inline
If you want to present the model inline, add the `present` method in the model:
```php
class User extends Model
{
    protected function present(Present $present)
    {
        $present->id();
        $present->timestamps();
    }
}
```


## Present The Model
### Present Column
You can add columns using `$this` reference like Blueprint (or `$present` when you use inline mode):
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

### Present Attribute

Add attribute with callback to define custom values:

```php
$this->attribute('is_artist', get: fn($model) => !is_null($model->artist_id));
```

Don't forget adding the `HasPresentAttributes`:

```php
class MyModel extends Model
{
    use HasPresentAttributes;
}
```

### Present Foreign
Add `foreignTo` to define foreign key.
```php
class PostPresent extends Present
{
    public function present()
    {
        $this->foreignTo(User::class);
    }
}
```

### Present Relations
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

Don't forget adding the `HasPresentAttributes`:

```php
class MyModel extends Model
{
    use HasPresentAttributes;
}
```

### Fillable
You can set fillable or not-fillable a column:
```php
$this->text('name')->fillable(); // Default is enabled
$this->text('icon')->fillable(false);
```

### Hidden
Or hide a column:
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

Add the `HasColumnFiles` trait to the model:
```php
use HasColumnFiles;
```

Now you can work with the file like this:
```php
$url = $model->file('image')->url();
$path = $model->file('image')->path();
$model->file('image')->delete();
```

Or add the `file` column and the `HasSelfFiles` trait:
```php
use HasSelfFiles;
```

To work with self file like this:
```php
$file = $model->file();
$url = $model->url();
$path = $model->path();
$model->file()->delete();
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
Storage::disk(Model::getDiskName('image'))->put('new-file.png', $file);
```

## Advanced

### Extension

Create custom extensions like this:

```php
class AvatarPresentExtension extends PresentExtension
{
    
    public function extend(Present $present)
    {
        $present->belongsTo(Avatar::class);
    }

}
```

Then use it anyway with `extend` method:

```php
class UserPresent extends Present
{
    
    public function present()
    {
        $this->id();
        $this->extend(AvatarPresentExtension::class);
        $this->timestamps();
    }
    
}
```

Or use it in a trait:

```php
trait HasAvatar
{
    public static bootHasAvatar()
    {
        static::extendPresent(AvatarPresentExtension::class);
    }
}
```

And use `yield` method in the present:

```php
class UserPresent extends Present
{
    
    public function present()
    {
        $this->id();
        $this->yield();
        $this->timestamps();
    }
    
}
```

And:

```php
class User
{
    use HasPresent, HasAvatar;
}
```


## Inheritance Present

Use the `atYield` method to extend the child present at the
`yield` section of parent:

```php
class PersonPresent extends Present
{
    public function present()
    {
        $this->id();
        $this->yield();
        $this->timestamps();
    }
}

class UserPresent extends PersonPresent
{
    public function present()
    {
        $this->atYield(parent::present(...), function ()
        {
            $this->string('name');
            $this->yield();
        });
    }
}

class AdminPresent extends PersonPresent
{
    public function present()
    {
        $this->atYield(parent::present(...), function ()
        {
            $this->string('role');
        });
    }
}
```

Now the column order is:
`id`, `name`, `role`, `created_at`, `updated_at`

You can use it in the inline presents too:

```php
class Person extends Model
{
    protected function present(Present $present)
    {
        $present->id();
        $present->yield();
        $present->timestamps();
    }
}

class User extends Model
{
    protected function present(Present $present)
    {
        $present->atYield(parent::present(...), function () use ($present)
        {
            $present->string('name');
            $present->yield();
        });
    }
}

class Admin extends Model
{
    protected function present(Present $present)
    {
        $present->atYield(parent::present(...), function () use ($present)
        {
            $present->string('role');
        });
    }
}
```
