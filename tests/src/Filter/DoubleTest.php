<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Double;

class DoubleTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Double();
    }
    
    function testFilter() {
        $this->filter->setOption(Double::OPTION_PRECISION, 3);
        $this->assertEquals(1234, $this->filter->filter(1234.0001));
        $this->assertEquals(1234.001, $this->filter->filter(1234.0012));
    }

    function testObject() {
        $obj = new \stdClass();
        $this->assertEquals($obj, $this->filter->filter($obj));
    }

    function testZero() {
        $this->assertEquals(0, $this->filter->filter('0'));
    }
}