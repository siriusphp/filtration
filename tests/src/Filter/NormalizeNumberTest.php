<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\NormalizeNumber;

class NormalizeNumberTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new NormalizeNumber();
    }
    
    function testDefaultOptions() {
        $this->assertEquals(12345.67, $this->filter->filter('12.345,67'));
        $this->assertEquals(12345.67, $this->filter->filter('12 345,67'));
    }

    function testAlreadyNormalizedNumbers() {
        $this->assertEquals(12345.67, $this->filter->filter('12345.67'));
    }
}