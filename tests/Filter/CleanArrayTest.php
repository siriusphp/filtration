<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class CleanArrayTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new CleanArray();
    }

    function testDefaultOptions()
    {
        $this->assertEquals(array('abc'), $this->filter->filter(array(0, '', 'abc', '0')));
    }

    function testAssociativeArray()
    {
        $this->assertEquals(array('k' => 'abc'), $this->filter->filter(array('', 'k' => 'abc', '')));
    }

    function testObject()
    {
        $obj = new \stdClass();
        $this->assertEquals($obj, $this->filter->filter($obj));
    }

    function testFilterSingle()
    {
        $this->assertEquals(array(), $this->filter->filterSingle(array('', '0')));
    }
}