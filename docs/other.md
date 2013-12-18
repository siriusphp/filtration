# Other uses of SiriusFiltration

## Output filtering

Most of the times, filters are used to sanitize incoming data but filters are just a way to convert alter data.
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

// somewhere in your app

$article = ArticleTable::getById(10);
$articleWrapper = new ArticleWrapper($article, Registry::get('article_filtrator'));

// anywhere within your plugins/modules
// assuming 'uppercase' and 'truncate' were registered as filters
Registry::get('article_filtrator')->add('title', 'uppercase');
Registry::get('article_filtrator')->add('title', 'truncate', array('length' => 15));

// in your views
echo $articleWrapper->title; // will print 'This Is The Best...' for an article that has the title 'This is the best filter library'
```

## Single value filtering

You may find yourself in a situation when you need to filter a single value (be it a string, number or an object), that is to alter a piece of data according to some rules.
For example, your CMS is displaying the content of an article which may have Markdown formatting and you don't want to employ a filter on the `ArticleWrapper` example above. You could "fake" the array like so

```php
$filtrator->add('random_key', $filter_you_want_to_be_applied);
$result = $filtrator->filter(array('random_key' => $value_that_you_want_filtrated));
return $result['random_key'];
```

Or you may create a `SingleValueFiltrator` class which simplifies the whole process.
