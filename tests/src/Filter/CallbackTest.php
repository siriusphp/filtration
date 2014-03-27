<?php

namespace Sirius\Filtration\Filter;

use Sirius\Filtration\Filter\Callback;


class FakeTestClass {
    
    static function returnThree($value) {
        return 3;
    }
    
    function returnFour($value) {
        return 4;
    }
}

class CallbackTest extends \PHPUnit_Framework_TestCase {
    
    function setUp() {
        $this->filter = new Callback();
    }
    
    
    function testStaticMethodAsString() {
        $args = array('some' => 'value');
        $this->filter->setOption(Callback::OPTION_CALLBACK, __NAMESPACE__.'\FakeTestClass::returnThree');
        $this->filter->setOption(Callback::OPTION_ARGUMENTS, $args);
        $this->assertEquals(FakeTestClass::returnThree('abc'), $this->filter->filter('abc'));
        
        // test the unique ID
        $uniqueId = $this->filter->getUniqueId();
        $this->assertTrue(strpos($uniqueId, __NAMESPACE__.'\Callback') === 0);
        $this->assertTrue(strpos($uniqueId, __NAMESPACE__.'\FakeTestClass::returnThree') !== false);
        $this->assertTrue(strpos($uniqueId, json_encode($args)) !== false);
    }

    function testStaticMethodAsArray() {
        $this->filter->setOption(Callback::OPTION_CALLBACK, array(__NAMESPACE__.'\FakeTestClass', 'returnThree'));
        $this->assertEquals(FakeTestClass::returnThree('abc'), $this->filter->filter('abc'));

        // test the unique ID
        $uniqueId = $this->filter->getUniqueId();
        $this->assertTrue(strpos($uniqueId, __NAMESPACE__.'\FakeTestClass::returnThree') !== false);
    }
    
    function testObjectMethod() {
        $obj = new FakeTestClass();
        $this->filter->setOption(Callback::OPTION_CALLBACK, array($obj, 'returnFour'));
        $this->assertEquals($obj->returnFour('abc'), $this->filter->filter('abc'));

        // test the unique ID
        $uniqueId = $this->filter->getUniqueId();
        $this->assertTrue(strpos($uniqueId, '->returnFour') !== false);
    }
    
    function testClosure() {
        $this->filter->setOption(Callback::OPTION_CALLBACK, function() {
            return 5;
        });
        
        $this->assertEquals(5, $this->filter->filter('abc'));
        // test the unique ID
        $uniqueId = $this->filter->getUniqueId(); // just to be registered by the coverage reporter
    }
}