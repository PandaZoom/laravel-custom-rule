## Laravel Custom Rule

Minimum requirement by php `8.1`

`composer require panda-zoom/laravel-custom-rule`;

Extend your rule from `PandaZoom\LaravelCustomRule\BaseCustomRule`
and implement method `passes` like on this class `Illuminate\Validation\Rules\Password`

### Using:

On rule list call as:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MyCustomRule; // your rule

class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
        // list of the default rules, missing exclude: required, nullable or sometimes
        'example_default_1' => [Locale::default()], // all default rules like as class instance
        'example_default_2' => MyCustomRule::toArray(), // all default rules as array
       
        'example_required' => MyCustomRule::required(), // all default rules + required
        'example_nullable' => MyCustomRule::nullable(), // all default rules + nullable
        'example_sometimes' => MyCustomRule::nullable(), // all default rules + sometimes
        ]; 
    }
}
```
