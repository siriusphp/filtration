<?php

namespace Sirius\Filtration\Filter;

use PHPUnit\Framework\TestCase;

class CensorTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Censor();
    }

    function testNoString()
    {
        $this->assertEquals(5, $this->filter->filterSingle(5));
    }

    function testDefaults()
    {
        $this->assertEquals('F**k, f**k the f*****g f*****s', $this->filter->filter('Fuck, fuck the fucking fuckers'));
    }

    function testOptionOverrides()
    {
        $this->filter->setOption(Censor::OPTION_END_CHARACTERS, 2);
        $this->assertEquals('F*ck, f*ck the f****ng f****rs', $this->filter->filter('Fuck, fuck the fucking fuckers'));
    }
}