---
title: The filter factory | Sirius PHP Filter
---

# The filter factory

Each filtrator object uses individuals filters which are constructed by a `FilterFactory`. The filtrator depends on the filter factory but if you don't provide it, one will be constructed for your

```php
use Sirius\Filtration\Filtrator;
use Sirius\Filtration\FilterFactory;

$filterFactory = new FilterFactory();
$filtrator = new Filtrator($filterFactory);
```

You can register your custom filters within the `FilterFactory` like so:

```php
$filterFactory->register('my_filter', 'MyApp\Filtration\Filter\MyFilter');
```

If you use dependency injection (and you should) you can have a single filter factory for your entire application and register all the filters you need at the beginning of the app.

If you don't want to do that you can always pass the class to the filtrator:

```php
$filtrator->add('content', 'MyApp\Filtration\Filter\MyFilter');
```

Also, if your filters are very complex, that is they have dependencies) you need to extend the filter factory.

```php
namespace MyApp\Filtration;

class FilterFactory extends Sirius\Filtration\FilterFactory {
    
    $protected $container = $container;

    function __construct(Container $container) {
        $this->container = $container;
    }
    
    function createFilter($callbackOrFilterName, $options = null, $resursive = false) {
        $filter = $this->container->get($callbackOrFilterName, [$options, $recursive]);
        if (!$filter) {
            $filter = parent::createFilter($callbackOrFilterName, $options, $resursive)
        }
        return $filter;
    }
    
}
```

Or something to that extent.