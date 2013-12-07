<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\StringTrim;

class StringTrimTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new StringTrim();
    }
    

    function testRecursivity() {
        $result = $this->filter->filter(array('   abc', 'def   '));
        $this->assertEquals(array('abc', 'def'), $result);
    }
    
    function testLeftTrim() {
        $this->filter->setOption(StringTrim::OPTION_SIDE, StringTrim::VALUE_SIDE_LEFT);
        
        $this->assertEquals('abc   ', $this->filter->filter('   abc   '));
    }

    function testRightTrim() {
        $this->filter->setOption(StringTrim::OPTION_SIDE, StringTrim::VALUE_SIDE_RIGHT);
    
        $this->assertEquals('   abc', $this->filter->filter('   abc   '));
    }
    
}