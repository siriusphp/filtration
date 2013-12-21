<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Truncate;

class TruncateTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Truncate();
        $this->string = 'abcde fgh ijkl mnopq rst';
    }
    

    function testNoString() {
        $this->assertEquals(5, $this->filter->filterSingle(5));
    }
    
    function testNoLimit() {
        $result = $this->filter->filterSingle($this->string);
        $this->assertEquals('abcde fgh ijkl mnopq rst', $result);
    }
    
    function testWordBreaking() {
        $this->filter->setOption(Truncate::OPTION_LIMIT, 7);
        $this->assertEquals('abcde f...', $this->filter->filterSingle($this->string));
    }

    function testNoWordBreaking() {
        $this->filter->setOption(Truncate::OPTION_LIMIT, 7);
        $this->filter->setOption(Truncate::OPTION_BREAK_WORDS, false);
        $this->assertEquals('abcde...', $this->filter->filterSingle($this->string));
    }
    
    function testShortString() {
        $this->filter->setOption(Truncate::OPTION_LIMIT, 10);
        $this->assertEquals('abcde', $this->filter->filterSingle('abcde'));
    }

    function testLimitTooLittle() {
        $this->filter->setOption(Truncate::OPTION_LIMIT, 3);
        $this->filter->setOption(Truncate::OPTION_BREAK_WORDS, false);
        $this->assertEquals('abcde...', $this->filter->filterSingle($this->string));
    }

    function testWordWithNoSpaces() {
        $this->filter->setOption(Truncate::OPTION_LIMIT, 5);
        $this->filter->setOption(Truncate::OPTION_BREAK_WORDS, false);
        $this->assertEquals('abcdefghijklmn', $this->filter->filterSingle('abcdefghijklmn'));
    }
    
}