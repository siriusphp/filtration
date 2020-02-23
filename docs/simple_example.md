---
title: Simple filter example | Sirius PHP Filter
---

# Simple example

Let's assume the data you want to filter comes from a form that represents a blog post


### Initialize the filtrator object

```php
require_once ('path/to/siriusfiltration/autoload.php');

$filtrator = new Sirius\Filtration\Filtrator;

// trim all the whitespace from input
$filtrator->add('*',
    'trim'  /* the function trim() will be applied to everything */,
    null    /* additional parameters for the filter */,
    true    /* recursively apply the filter */,
    100     /* the priority of the filter if you don't want to use the order they are added*/);

// strip the tags from the blog title
$filtrator->add('title', 'strip_tags');

// strip all but the P, DIV and BR tags from the content
$filtrator->add('content', 'strip_tags', ['<p><div><br><br/>']);
```

### Get access to the clean data

```php
$filteredPostData = $filtrator->filter($_POST);
```

