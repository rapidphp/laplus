# Docblock Generator

## Command Line

Use this command to update the docblock:

```shell
php artisan docblock+
```

![Generated Docblock](screen_docblock.png)

## Present

Presented attributes will generate:

```php
public function present()
{
    $this->id();
    $this->string('name');
}
```

Output document:

```php
/**
 * @property int $id
 * @property string $name
 */
```

### Hint

Customize the hint document by `typeHint` and `docHint`:

```php
public function present()
{
    $this->string('name')->typeHint(MyString::class . '|string')->docHint('This is a test string');
}
```

Output document:

```php
/**
 * @property MyString|string $name This is a test string
 */
```


## Model Attributes

Laravel model attributes will generate as well:

```php
public function getFullNameAttribute() : string
{
    return $this->firstName . ' ' . $this->lastName;
}

public function setFamilyAttribute(string|int $value)
{
    $this->lastName = $value;
}
```

Output document:

```php
/**
 * @property string $full_name
 * @property string|int $family
 */
```


## Marked With Attributes

Mark what you want with `DocblockAttributeContract` interface attribute to
generate in the docblock output.

### Is Relation

Mark the relating methods to generate in the docblock:

```php
#[IsRelation]
public function user()
{
    return $this->belongsTo(User::class);
}
```

Output document:

```php
/**
 * @property ?User $user
 */
```


## Labels

Label translator is supported as well:

```php
class UserLabelTranslator extends LabelTranslator
{
    public function name(bool $emoji = false)
    {
        return '...';
    }
    
    public function gender(bool $emoji)
    {
        return '...';
    }
}
```

Output document:

```php
/**
 * @property string $name_label
 * @property string name_label(bool $emoji = false)
 * @property string gender_label(bool $emoji)
 */
```
