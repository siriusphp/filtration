---
title: Filtering output | Sirius PHP Filter
---

# Output filtering

Most of the times, filters are used to sanitize incoming data but filters are just a way to convert/alter data.
There are situations when the data that is stored by your app must be displayed in a different way; dates might be localized, article content might be truncated etc.
If your application is pluggable you might also want to allow other developers to inject their own filters

Below is a simple example on how you might use the filtrator with your data for output.

```php

class ArticleWrapper {
	protected $filtrator;
	
	protected $article;

	function __construct($article, $filtrator) {
		$this->article = $arcticle;
		$this->filtrator = $filtrator;
	}
	
	function __get($name) {
		return $this->filtrator->filterItem($article->{$name}, $name);
	}
	
}
```

somewhere in your app

```php
$article = ArticleTable::getById(10);
$filtrator = new Sirius\Filtration\Filtrator;
$filtrator->add('title', 'ucwords');
$filtrator->add('title', 'truncate', ['length' => 15]);

$articleWrapper = new ArticleWrapper($article, $filtrator);
```

and in your views

```php
echo $articleWrapper->title; // will print 'This Is The Best...' for an article that has the title 'This is the best filter library'
```