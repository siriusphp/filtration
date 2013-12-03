<?php

namespace Sirius\Filtration\Test;

use Sirius\Filtration\Filtrator;

function postFiltrationFunction($value) {
    return $value . '.post';
}

function preFiltrationFunction($value) {
    return 'pre.' . $value;
}

class FiltratorTest extends \PHPUnit_Framework_TestCase  {

    function setUp() {
        $this->filtrator = new Filtrator();
        $this->data = array(
            'whitespace' => '   some string   ',
            'html' => '   <strong><em>html</em></strong>',
            'array' => array(
                'whitespace' => '   some string   ',
                'array' => array(
                    'whitespace' => '  some string   '
                )
            )
        );
    }

    function testRootFilters() {
    	$this->filtrator->add('/', function($value) {
        	$value['text'] = trim(strip_tags($value['html']));
        	return $value;
    	});
    	$filtered = $this->filtrator->filter($this->data);
    	$this->assertEquals('html', $filtered['text']);
    }
    
    function testSelectorPath() {
        $this->filtrator->add('array[whitespace]', 'StringTrim');
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('   some string   ', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testFilterRecursivity() {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('some string', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testFilterRemoval() {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $this->filtrator->remove('*', 'StringTrim');
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('   some string   ', $filtered['whitespace']);
    }

    function testFilterPriority() {
        $this->filtrator
            ->add('whitespace', 'StringTrim')
            ->add('whitespace', __NAMESPACE__.'\postFiltrationFunction', null, false, -1)
            ->add('whitespace', __NAMESPACE__.'\preFiltrationFunction', null, false, -1);
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('pre.   some string   .post', $filtered['whitespace']);
    }

    function testFilteringSingleValue() {
        $this->filtrator->add('*', 'StringTrim', null, true);
        $this->assertEquals(array('some string'), $this->filtrator->filter(array('   some string   ')));
    }

    function testDuplicateCallbacksNotAllowed() {
        $this->filtrator->add('*', 'StringTrim', null, true);
        $this->filtrator->add('*', 'StringTrim', null, true);

        $this->assertEquals(1, count($this->filtrator->getAll()['*']));
    }

    function testExceptionThrownForUncallableFilters() {
        $this->setExpectedException('\InvalidArgumentException');
        $this->filtrator->add('*', 'hopefully_this_is_not_a_valid_function');
    }

    function testRemovingAllFiltersForASelector() {
        $this->filtrator
            ->add('whitespace', __NAMESPACE__.'\postFiltrationFunction')
            ->add('whitespace', __NAMESPACE__.'\preFiltrationFunction');
        $this->filtrator->remove('whitespace', true);
        $this->assertEquals(array(), $this->filtrator->getAll());    
    }

}