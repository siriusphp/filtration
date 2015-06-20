---
title: Using Sirius\Filtration for models
---

# Filtration for your models

Usually models are populated with data from a form but that is not always the case. 
If you use an API you might want to use a filtrator object before populating your models but you can use a filtrator object directly on inside your models.

Below is a simple example on how you might use the filtrator with your models

```php
class Customer {
	protected $filtrator;
	
	protected $name;
	protected $birthdate;
	protected $email;
	protected $address;

	function __construct() {
		// you may choose to implement this differently
		$filtrator = new \Sirius\Filtration\Filtrator;
		$filtrator
			->add('name', 'stringtrim')
			->add('birthdate', 'normalizedate');
	}
	
	function setName($name) {
		$this->name = $this->filtrator->filterItem($name, 'name');
		return $this;
	}
	
	function setBirtdate($date) {
		$this->birtdate = $this->filtrator->filterItem($date, 'birtdate');
		return $this;
	}
}
```

somewhere in your app

```php
$customer = new Customer();
$customer->setName('  My Name  '); // converted to 'My Name' 
$customer->setBirtdate('20/11/2013'); // converted to '2013-11-20'
```