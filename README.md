#Sirius\Filtration

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


## Links

- [documentation](http://www.sirius.ro/php/sirius/validation/)
- [changelog](CHANGELOG.md)
