<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class StringTrimTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new StringTrim();
    }

    function testNoString()
    {
        $this->assertEquals(5, $this->filter->filterSingle(5));
    }

    function testRecursivity()
    {
        $result = $this->filter->filter(array('   abc', 'def   '));
        $this->assertEquals(array('abc', 'def'), $result);
    }

    function testLeftTrim()
    {
        $this->filter->setOption(StringTrim::OPTION_SIDE, StringTrim::VALUE_SIDE_LEFT);

        $this->assertEquals('abc   ', $this->filter->filter('   abc   '));
    }

    function testRightTrim()
    {
        $this->filter->setOption(StringTrim::OPTION_SIDE, StringTrim::VALUE_SIDE_RIGHT);

        $this->assertEquals('   abc', $this->filter->filter('   abc   '));
    }

}