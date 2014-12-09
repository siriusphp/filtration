#Sirius\Filtration

[![Build Status](https://travis-ci.org/siriusphp/filtration.png?branch=master)](https://travis-ci.org/siriusphp/filtration)
[![Coverage Status](https://coveralls.io/repos/siriusphp/filtration/badge.png)](https://coveralls.io/r/siriusphp/filtration)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/siriusphp/filtration/badges/quality-score.png?s=1897aacdd8313df10089c5307b336c0fde8624db)](https://scrutinizer-ci.com/g/siriusphp/filtration/)
[![Latest Stable Version](https://poser.pugx.org/siriusphp/filtration/version.png)](https://packagist.org/packages/siriusphp/filtration)
[![License](https://poser.pugx.org/siriusphp/filtration/license.png)](https://packagist.org/packages/siriusphp/filtration)

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

// add filters for title
$filtrator->add('title', 'trim');
$filtrator->add('title', 'strip_tags');
$filtrator->add('title', 'nullify');

// add filters for content in one go
$filtrator->add('content', [
	'trim'
]);

$result = $filtrator->filter(array(
	'title' => '   <h1>My title has tags and is awesome</h1>',
	'content' => '   My content was trimmed'
));

/* $result is
array(
	'title' => NULL ,
	'content' => 'My content was trimmed'
)
*/
```

##Why (just) arrays?

Arrays are easily exchangeable between components. User input (POST or GET) is an array, an object may be populated from an array, database query row can be an array, JSON data is passed from the client and gets to the server as an array. 

##Documentation

- [general usage of the library](docs/index.md)
- [built-in filters](docs/filters.md)
- [using SiriusFiltration with models](docs/modeling.md)
- [other usages for SiriusFiltration](docs/other.md)
