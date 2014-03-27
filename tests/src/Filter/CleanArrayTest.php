<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\CleanArray;

class CleanArrayTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new CleanArray();
    }
    
    function testDefaultOptions() {
        $this->assertEquals(array('abc'), $this->filter->filter(array(0, '', 'abc', '0')));
    }

    function testAssociativeArray() {
        $this->assertEquals(array('k' => 'abc'), $this->filter->filter(array('', 'k' => 'abc', '')));
    }

    function testObject() {
        $obj = new \stdClass();
        $this->assertEquals($obj, $this->filter->filter($obj));
    }
    
    function testFilterSingle() {
        $this->assertEquals(array(), $this->filter->filterSingle(array('', '0')));
    }
}