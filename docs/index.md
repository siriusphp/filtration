# How to use SiriusFiltration

```php
require_once ('path/to/siriusfiltration/autoload.php');

$filtrator = new Sirius\Filtration\Filtrator;

// syntax for adding filters
$filtrator->add($selector, $callbackOrFilterName, $options = null, $recursive = false, $priority = 0);

// strip all but the P, DIV and BR tags from the content
$filtrator->add('content', 'strip_tags', array('<p><div><br><br/>'));

// trim all the elements of the array, only on the first level
$filtrator->add('*', 'trim');

// trim all the elements of the array, recursively
$filtrator->add('*', 'trim', null, true);

$filteredPostData = $filtrator->filter($_POST);
```

The `$callbackOrFilterName` parameter can be 

####1. a class name that extends `\Sirius\Filtration\Filter\AbstractFilter`
```php
$filtrator->add('slug', '\MyApp\Filtration\Filter\Sluggify');
```

####2. a class name that belongs to the `\Sirius\Filtration\Filter` namespace
```php
$filtrator->add('slug', 'StringTrim');
```

####3. a filter registered within the filtrator
```php
$filtrator->registerFilterClass('sluggify', '\MyApp\Filtration\Filter\Sluggify');
$filtrator->add('slug', 'sluggify');
```

####4. anything that is callable: a PHP function, a static method class, an invokable object etc. 
The only things to keep in mind are:

- The first argument must be the value you want filtered. `trim`, `strtolower`, `ucwords` are good candidates, but not `str_replace`.
- The parameters passed to the callback will be added one after the other
- Some PHP function will throw warnings if you pass more variables than expected and SiriusFiltration adds the context as the last parameter of any callback

```php
function myFilter($value, $arg1, $arg2, $arg3) {
    // this is your filter function
}

$filtrator->add('selector', 'myFilter', array(1, 2, 3));
```

## Removing filters

Sometimes you may want to remove filters (if your app uses events to alter its functionality).
You can do that like this:

```php
// remove a single filter that is a callback
$filtrator->remove('title', 'trim');

// remove a single filter that is a class
$filtrator->remove('slug', '\MyApp\Filtration\Filter\Sluggify');

// remove a single filter that is a registered filter
$filtrator->remove('slug', 'sluggify');

// remove all filters
$filtrator->remove('*', true);
```

## Transforming data

Sometimes the data provided may come in "wrong" shape. For example a date field may be set in $_POST as an array on a different key but you only need a regular string to manipulate.
For this situations you need to transform the data, not filter it.

```php
$data = array(
    'date_as_array' => array(
        'year' => 2012,
        'month' => 1,
    	'day' => 12
    )
);
function convertDateArraysToString ($data, $source, $destination) {
	$data[$destination] = sprintf('%s-%s-%s, $data[$source]['year'], $data[$source]['month'], $data[$source]['day']);
	// make sure you return the data back
	return $data;
}

$filtrator->add(Filtrator::SELECTOR_ROOT, 'convertDateArraysToString', array('date_as_array', 'date')); 
$data = $filtrator->filter($data);
$data['date'] == '2012-1-12'; // true
```

## Filtering only one array element

Sometimes you may need to filter a single value. For example, you may have a filtrator object that you use for a form but you use AJAX to send a single value to the server; you still need to filter the value but you don't want to repeat yourself

```php
$filteredValue = $filtrator->applyFilters('key[subkey]', $_POST['key']['subkey']);
```
The code above will apply all the filters associated with the selector that match the `key[subkey]` (`key[*]` or `*[*]` but not `*`) to the value passed as the second parameter.

## Get the list of your filters

You may need to retrieve the list of filters for various reasons (eg: you need to converted into a list of javascript filters for the client side)
```php
$filters = $filtrator->getAll();
// returns an array
array(
    'selector' => array(
        0 => array(
            'callback' => 'filter_callback',
            'params' => array(1, 2, 3),
            'recursive' => true
        )
    )
);
```

## Caveats

#### 1. You cannot filter single values... easily.

```php
// you cannot have something like
$filteredString = $filtrator->filter('single string');

// but you can filter fake it
$filteredString = $filtrator->filter(array('single_string'))[0];
```

#### 2. You cannot add the same callback twice

```php
// you may want to do something like
$filtrator->add('selector', 'trim', array("\n\t"));
// and later on
$filtrator->add('selector', 'trim', array(" "));
// but the second add() will not add the filter on the stack
// you can however do it like this
$filtrator->add('selector', function($value) {
    return trim($value, ' ');
});
```
