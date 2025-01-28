# Laplus Label
Labels are the string-converter for attributes.

## Make Model With Label
You can use this command to create a model with label:
```shell
php artisan make:model+ Name --label
```

This command will create `app/Models/Name.php` model, `app/Presents/NamePresent` present
and `app/LabelTranslator/NameLabelTranslator` label.


## Create Manually
1- Create label for the model using:
```shell
php artisan make:label-translator NameLabelTranslator
```

2- Use the `HasLabels` trait in the model:
```php
class Name extends Model
{
    use HasLabels;
}
```

## Add Label
For adding label, add a methods named your attributes, in the translator:
```php
class UserLabelTranslator extends LabelTranslator
{
    public function age()
    {
        return $this->value . " years old";
    }
    
    public function role()
    {
        return match ($this->value) {
            0 => "Developer",
            1 => "Admin",
            2 => "Member",
        };
    }
    
    public function emailVerifiedAt()
    {
        return $this->asDateTime;
    }
    
    public function is_male()
    {
        return $this->asYesNo;
    }
}
```

## Use Label
You have three way to get labels from record:
```php
// Use the _label suffix:
echo "User role is {$user->role_label}";

// Use the _label suffix as method:
echo "User role is {$user->role_label()}";

// Use the label() method:
echo "User role is {$user->label('role')}";
```

## Labels With Arguments
```php
class PostLabelTranslator extends LabelTranslator
{
    public function categories(int $max = 3)
    {
        return $this->record->categories()->limit($max)->pluck('name')->implode(", ");
    }
}
```

Usage:
```php
// Use the _label suffix (parameters will be defaults):
echo "Post Categories: {$post->categories_label}";

// Use the _label suffix as method:
echo "Post Categories: {$post->categories_label(max: 10)}";

// Use the label() method and pass parameters:
echo "Post Categories: {$post->label('categories', 10)}";
```

## Special Labels
### Builtin
Null, true and false, will translate in two levels

1- Methods: Add the following method in the LabelTranslator:
```php
class UserLabelTranslator extends LabelTranslator
{
    protected function getUndefined() : string
    {
        return "This value is not set.";
    }
    
    protected function getTrue() : string
    {
        return "This value is True.";
    }
    
    protected function getFalse() : string
    {
        return "This value is False.";
    }
}
```

2- Run following command to publish language files:
```shell
php artisan vendor:publish --tag="laplus:lang"
```

### Objects
If label value that returned in `LabelTranslator` is an object, Laplus try to call
    `getTranslatedLabel` in the object (or throw exception in otherwise)

```php
enum Gender : string
{
    case Male = 'Male';
    case Female = 'Female';
    
    public function getTranslatedLabel()
    {
        return match ($this) {
            self::Male => "MALE",
            self::Female => "FEMALE",
        };
    }
}

class UserLabelTranslator extends LabelTranslator
{
    public function gender()
    {
        return $this->value; // Value is type of Gender
    }
}

class MyController extends Controller
{
    public function showGender(User $user)
    {
        echo "User gender is {$user->gender_label}";
    }
}
```
