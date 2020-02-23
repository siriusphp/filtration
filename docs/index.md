Sirius Filtration

[![Source Code](http://img.shields.io/badge/source-siriusphp/filtration-blue.svg?style=flat-square)](https://github.com/siriusphp/filtration)
[![Latest Version](https://img.shields.io/packagist/v/siriusphp/filtration.svg?style=flat-square)](https://github.com/siriusphp/filtration/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/siriusphp/filtration/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/siriusphp/filtration/master.svg?style=flat-square)](https://travis-ci.org/siriusphp/filtration)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/siriusphp/filtration.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/filtration/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/siriusphp/filtration.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/filtration)

PHP library for array filtering/sanitization

Sometimes you want to make sure the values pushed by a source (eg: a user when submits a form) follow some restrictions like

- no space at the beginning or the end for the title of a page
- no HTML code in a comment sent by a user
- no spaces in the field which represents the URL
- remove XSS attacks
- etc...

Other times you want to make sure that the data you send to the user is parsed before displaying. For example you may want to:

- convert markdown into HTML
- convert URLs into links
- apply a localized format to dates
- etc ()

To achieve this end result you need to filter the values. This is where SiriusFiltration comes into place

## Elevator pitch

```php
use Sirius\Filtration\Filtrator;

$filtrator = new Filtrator();

// add filters for content
$filtrator->add('content', 'trim');

// add filters for titles in
$filtrator->add('title', [
	'trim',
    'strip_tags',
    'nullify'
]);

$result = $filtrator->filter([
	'title' => '   <h1>My title has tags and is awesome</h1>',
	'content' => '   My content was trimmed'
]);

$result == [
	'title' => NULL, // because it contains only HTML
	'content' => 'My content was trimmed'
];
```

# How to use Sirius\Filtration

## Add filters

The second parameter can be:

##### - a class name that extends `\Sirius\Filtration\Filter\AbstractFilter`
```php
$filtrator->add('slug', '\MyApp\Filtration\Filter\Sluggify');
```

##### - a class name that belongs to the `\Sirius\Filtration\Filter` namespace
```php
$filtrator->add('slug', 'StringTrim');
```

##### - a filter registered within the filter factory

The filtrator depends on a FilterFactory
```php
// create an instance of the filter factory; here through a DIC
$filterFactory = $dependencyInjectionContainer->get('Sirius\Filtration\FilterFactory');
$filterFactory->registerFilter('sluggify', '\MyApp\Filtration\Filter\Sluggify');

// inject the factory into the filtrator
$filtrator = new Filtrator($filterFactory);
$filtrator->add('slug', 'sluggify');
```

##### - a callable: a PHP function, a static method class, an invokable object etc.
The only things to keep in mind are:

- The first argument must be the value you want filtered. `trim`, `strtolower`, `ucwords` are good candidates, but not `str_replace`.
- The parameters passed to the callback will be added one after the other
- Some PHP function will throw warnings if you pass more variables than expected and `Sirius\Filtration` adds the context as the last parameter of any callback

```php
function myFilter($value, $arg1, $arg2, $arg3) {
    // this is your filter function
}

$filtrator->add('selector', 'myFilter', [1, 2, 3]);
```

The library comes with a list of [built-in filters](docs/filters.md)

### the `$options` parameter can be:

1. An associative array that will be passed to the filtrator class
2. A non-associative array of arguments that will be passed to the callback
3. A JSON string (will be converted into an array using `json_decode`)
4. A jquery string (will be converted into an array using `parse_str`)

## Set allowed items

If you want to ensure that you don't get unwanted data you specify which item are allowed. Any other data will be excluded:

```php
$filtrator->setAllowedSelectors([
    'name',
    'description',
    'products', // if not provided, this will be inferred from the next rules
    'products[*][name]',
    'products[*][description]'
]);
```

If the data to be filtered contains, say a `user_id` element, it will be excluded from the array. 

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
For this situations you need to transform the data, not 'really' filter it.

```php
$data = [
    'date_as_array' => [
        'year' => 2012,
        'month' => 1,
    	'day' => 12
    ]
];
function convertDateArraysToString ($data, $source, $destination) {
	$data[$destination] = sprintf('%s-%s-%s', $data[$source]['year'], $data[$source]['month'], $data[$source]['day']);
	// make sure you return the data back
	return $data;
}

$filtrator->add(Filtrator::SELECTOR_ROOT, 'convertDateArraysToString', ['date_as_array', 'date']);
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
$filters = $filtrator->getFilters();
// returns an array
[
    'selector' => [
        0 => [
            'callback' => 'filter_callback',
            'params' => [1, 2, 3],
            'recursive' => true
        ]
    ]
];
```

## Caveats

#### 1. You cannot filter single values... easily.

```php
// you cannot have something like
$filteredString = $filtrator->filter('single string');

// but you can filter fake it
$filteredString = $filtrator->filter(['single_string'])[0];
```
