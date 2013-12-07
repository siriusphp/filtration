<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\NormalizeDate;

class NormalizeDateTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new NormalizeDate();
    }
    
    function testDefaultOptions() {
        $this->assertEquals('2012-12-01', $this->filter->filter('01/12/2012'));
    }

    function testDateTimeNormalization() {
        $this->filter->setOption(NormalizeDate::OPTION_INPUT_FORMAT, 'm/d/Y H:i');
        $this->filter->setOption(NormalizeDate::OPTION_OUTPUT_FORMAT, 'Y-m-d H:i');
        $this->assertEquals('2012-11-10 23:05', $this->filter->filter('11/10/2012 23:05'));
    }
}