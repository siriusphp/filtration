<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Nullify;

class NullifyTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Nullify();
    }
    
    function testEmptyString() {
        $this->assertTrue(null === $this->filter->filter(''));
    }

    function testZero() {
        $this->assertTrue(null === $this->filter->filter(0));
    }

    function testNotNull() {
        $this->assertTrue(123 === $this->filter->filter(123));
        $this->assertTrue('abc' === $this->filter->filter('abc'));
    }
    
}