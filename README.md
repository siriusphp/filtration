#SiriusFiltration

[![Build Status](https://travis-ci.org/adrianmiu/sirius-validation.png?branch=master)](https://travis-ci.org/adrianmiu/sirius-filtration)
[![Coverage Status](https://coveralls.io/repos/adrianmiu/sirius-validation/badge.png)](https://coveralls.io/r/adrianmiu/sirius-filtration)

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

##Why (just) arrays?

Arrays are easily exchangeable between entities. User input (POST or GET) is an array, an object may be populated from an array, database query row can be an array, JSON data is passed from the client and gets to the server as an array. 

If you need something more "object-oriented" you can use Zend Framework 2 Input Filter library.

##Documentation

- [general usage of the library](docs/index.md)
- [using SiriusFiltration with models](docs/modeling.md)
- [other usages for SiriusFiltration](docs/other.md)
