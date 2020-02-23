<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class NullifyTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Nullify();
    }

    function testEmptyString()
    {
        $this->assertTrue(null === $this->filter->filter(''));
    }

    function testZero()
    {
        $this->assertTrue(null === $this->filter->filter(0));
    }

    function testNotNull()
    {
        $this->assertTrue(123 === $this->filter->filter(123));
        $this->assertTrue('abc' === $this->filter->filter('abc'));
    }

}