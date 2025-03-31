# Validation Generator

## Introduction

```php
class User extends Model
{
    use HasPresent, HasRules;
    
    protected function present(Present $present)
    {
        $present->id();
        $present->string('name');
        $present->string('email')->unique()->dataTypeRule(['string', 'max:255', 'email']);
        $present->enum('position', PositionEnum::class);
        $present->timestamps();
    }
} 
```

Then use it:

```php
public class CreateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            ...User::rules()->forCreate(),
        ];
    }
}

public class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            ...User::rules()->forUpdate(request()->route('user')),
        ];
    }
}

public class FindUserByEmailRequest extends FormRequest
{
    public function rules()
    {
        return [
            ...User::rules()->forFind()->only('email'),
        ];
    }
}
```
