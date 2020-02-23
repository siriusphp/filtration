<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class ObfuscateTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Obfuscate();
    }

    function testNoString()
    {
        $this->assertEquals(5, $this->filter->filterSingle(5));
    }

    function testDefaults()
    {
        $this->assertEquals('******', $this->filter->filter('secret'));
    }

    function testStartCharacters()
    {
        $this->filter->setOption(Obfuscate::OPTION_START_CHARACTERS, 2);
        $this->assertEquals('se****', $this->filter->filter('secret'));
    }

    function testEndCharacters()
    {
        $this->filter->setOption(Obfuscate::OPTION_END_CHARACTERS, 2);
        $this->assertEquals('****et', $this->filter->filter('secret'));
    }

    function testReplacement()
    {
        $this->filter->setOption(Obfuscate::OPTION_REPLACEMENT_CHAR, 'x');
        $this->assertEquals('xxxxxx', $this->filter->filter('secret'));
    }
}