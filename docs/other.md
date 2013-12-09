# Other uses of SiriusFiltration

Most of the times there are differences between the way an aplication stores its data and the way that data is displayed. Dates might be localized, article content might be truncated etc.
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