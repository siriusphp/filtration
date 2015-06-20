---
title: Custom filters
---

# Custom filters

Hopefully your app is complex enough to require you to build custom filters. Or you will find many uses for this library that you will have to build custom filters.

Below is the code for a filter that adds a sequence of underscores in front of every item that is passed to it **if** the 'append_underscores' element from the context is true

```php
namespace MyApp\Filtration\Filter;

class AppendUnderscores extends Sirius\Filtration\Filter\AbstractFilter {

    function filterSingle($value, $valueIdentifier = null) {
        // the context is passed to each filter and represents the entire dataset 
        // that was sent to be filtered 
        $no_of_underscores = (int) $this->context['append_underscores']
        return str_repeat('_', $no_of_underscores) . $value;
    }

}
```

And it is used like so

```php
use Sirius\Filtration\Filtrator;
$filtrator = new Filtrator();
$filtrator->add('content', 'MyApp\Filtration\Filter\AppendUnderscores');

$result = $filtrator->filter(array(
    'append_underscores' => 10,
    'content' => 'Pretty stupid, right?'
));

// $result will be
// array(
//    'append_underscores' => 10,
//    'content' => '_________Pretty stupid, right?'
// )
```