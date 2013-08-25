How to use SiriusFiltration
======

```php
require_once ('path/to/siriusfiltration/autoload.php');

$filtrator = new Sirius\Filtration\Filtrator;

// syntax for adding filters
$filtrator->add($selector, $callback, $parametersForTheCallback = null, $priority = 0, $recursive = false);


// trim all the elements of the array, only on the first level
$filtrator->add('*', 'trim');

// trim all the elements of the array, recursively
$filtrator->add('*', 'trim', null, 0, true);

// strip all but the P, DIV and BR tags from the content
$filtrator->add('content', 'strip_tags', array('<p><div><br><br/>'));

// remove a filter
$filter->remove('*', 'ucwords');

```

The `$callback` parameter can be any callable entity: a PHP function, a static method class etc. 
The only things to keep in mind are:

1. The first argument must be the value you want filtered. `trim`, 'strtolower', 'ucwords' are good candidates, but not `str_replace`.
2. The parameters passed to the callback will be added one after the other
```php
function myFilter($value, $arg1, $arg2, $arg3) {
    // this is your filter function
}

$filtrator->add('selector', 'myFilter', array(1, 2, 3));
```
