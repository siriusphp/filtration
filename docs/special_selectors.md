---
title: Syntactic sugar
---

# Special data selectors


### The `any` selector 

The `Sirius\Filtration` library manipulates the data within arrays and sometimes you apply some filters to all elements of the array. For example you many not want to allow users to provide a space character on some fields (to bypass the 'required' validation rule).

In this case you do something like this:

```php
$filtrator->add('*', 'stringtrim | nullify')
```

### The `root` selector

Sometimes you want to be able to alter the entire dataset the user sent to your app. For example, let's say you have a form where a date element is split into 3 input fields (year, month, day) that also have a different name but you want your app to work with a single field.

In this case you do something like this:
```php
function convertDateArrays($value, $options, $valueIdentifier, $context) {
    // here $value will be the entire dataset
    $value['date'] = sprintf('%s-%s-%s', $value['__date']['year'], $value['__date']['month'], $value['__date']['day']);
    unset($value['__date']);
}

$filtrator->add('/', 'convertDateArrays');

$result = $filtrator->filter([
    '__date' => ['year'=> 2000, 'month' => 10, 'day' => 5],
]);

// $result will be
// [
//     'date' => '2000-10-5'
// ]
```

**Important!** The `root` filters are applied first so you can also use it to globally clean all of your data