<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Integer();
    }

    function testFilter()
    {
        $this->assertEquals(1234, $this->filter->filter(1234));
        $this->assertEquals(1234, $this->filter->filter(1234.56));
    }

    function testObject()
    {
        $obj = new \stdClass();
        $this->assertEquals($obj, $this->filter->filter($obj));
    }

    function testZero()
    {
        $this->assertEquals(0, $this->filter->filter('0'));
    }
}