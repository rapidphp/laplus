
# Travels

Once a version of the project has been deployed, it is almost impossible to change the database.

Travels allow you to make changes to the table records as well as change the table structure. ✈️

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public string|array $whenAdded = 'full_name';

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


## Making New Travel

Use following command to create new travel:

```shell
php artisan make:travel your_name
```

## Properties

### Connect to the models

Define the `$on` property and define the depended model (or models):

```php
return new class extends Travel
{
    public string|array $on = [User::class, Post::class];
}
```

### Set timing

Travels should be executed between migrations. But you need to specify when and at what
point they should be executed with the help of these properties.

**1- When a column added:**

Define the `$whenAdded` property and specify what columns should be executed after
they are added to the table.

If you are using one model, you only need to write the column names,
and if you are using multiple models, you can specify which model each column belongs to.

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public string|array $whenAdded = 'full_name';
}
```

```php
return new class extends Travel
{
    public string|array $on = [User::class, Post::class];
    public string|array $whenAdded = [User::class . '.public_name', Post::class . '.author_name'];
}
```

**2- When a column changed:**

By defining `$whenChanged` you can specify that it should be executed when a column changes.

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public string|array $whenChanged = 'balance';
}
```

**3- When a column renamed:**

Or you can specify to run when a column is renamed.

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public array $whenRenamed = ['full_name' => 'name']; // from full_name to name
}
```

**4- Before all migrations:**

By default, Laplace looks for the best position to add Travel,
but by adding `$anywayBefore` you can specify that this Travel should be run before all
other migrations.

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public bool $anywayBefore = true;
}
```

**5- After all migrations:**

By adding `$anywayFinally` you can specify that this Travel should be run after all other migrations.

```php
return new class extends Travel
{
    public string|array $on = User::class;
    public bool $anywayFinally = true;
}
```

### Temporary nullable a column

Sometimes adding a column will result in an error because it is not nullable!
Because there is no data in it. By adding that column to `$prepareNullable`,
Travel will be executed in such a way that first the column is added as nullable,
then Travel will be executed, and finally the nullability of that column will be removed!

```php
return new class extends Travel
{
    public string|array $on = Post::class;
    public string|array $whenAdded = 'slug';
    public string|array $prepareNullable = ['slug'];
    
    // Generating slugs...
}
```
