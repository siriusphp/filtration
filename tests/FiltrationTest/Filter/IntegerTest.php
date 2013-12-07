<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Integer;

class IntegerTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Integer();
    }
    
    function testFilter() {
        $this->assertEquals(1234, $this->filter->filter(1234));
        $this->assertEquals(1234, $this->filter->filter(1234.56));
    }

    function testObject() {
        $obj = new \stdClass();
        $this->assertEquals($obj, $this->filter->filter($obj));
    }
    
    function testZero() {
        $this->assertEquals(0, $this->filter->filter('0'));
    }
}