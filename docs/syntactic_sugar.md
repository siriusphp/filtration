---
title: Syntactic sugar | Sirius PHP Filter
---

# Syntactic sugar


##### 1. Add multiple rules at once by using just a string
```php
// separate rules using ' | ' (space, pipe, space)
$filtrator->add('email', 'stringtrim | nullify');
```

##### 2. Add rule with parameters
```php
// or parameters set as query string
$filtrator->add('name', 'stringtrim(side=both)');
// parameters set as JSON string, recursive, priority = 10
$filtrator->add('paragraphs', 'stringrim({"side":"both"})(true)(10)');

// the above example is similar to
$filtrator->add('name', 'stringtrim', ['side' => 'both'], true, 10);
```

**Important!** You cannot have something like `(this)` inside the JSON or query string.

##### 3. Mix and match 1 and 2
```php
$filtrator->add('name', 'stringtrim(side=both) | nullify');
```

**Important!** The sequence ` | ` cannot be inside the parameters.

##### 4. Add multiple rules per selector
```php
$filtrator->add(
    // add the label after the selector so you don't have to pass the label to every rule
    'name', 
    [
        'stringtrim',
        'nullify'
    ]
);
```

##### 5. Add multiple rules on multiple selectors
Mix and match everthing from above
```php
$filtrator->add([
    'title' => 'stringtrim | strip_tags | nullify',
    'content' => 'stringtrim | nullify',
]);
```