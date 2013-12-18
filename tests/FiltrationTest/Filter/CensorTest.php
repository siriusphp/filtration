<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Censor;

class CensorTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Censor();
    }
    
    function testDefaults() {
        $this->assertEquals('F**k, f**k the f*****g f*****s', $this->filter->filter('Fuck, fuck the fucking fuckers'));
    }
    
    function testOptionOverrides() {
        $this->filter->setOption(Censor::OPTION_END_CHARACTERS, 2);
        $this->assertEquals('F*ck, f*ck the f****ng f****rs', $this->filter->filter('Fuck, fuck the fucking fuckers'));
    }
}