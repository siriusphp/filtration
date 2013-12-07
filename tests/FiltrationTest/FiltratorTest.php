<?php
namespace Sirius\Filtration;

use Sirius\Filtration\Filtrator;

function postFiltrationFunction($value)
{
    return $value . '.post';
}

function preFiltrationFunction($value)
{
    return 'pre.' . $value;
}

class FakeFilter extends Filter\AbstractFilter {

    function filterSingle($value, $valueIdentifier = null) {
        return 'fake';
    }
}

class FiltratorTest extends \PHPUnit_Framework_TestCase
{

    function setUp()
    {
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

    function testRootFilters()
    {
        $this->filtrator->add('/', function ($value)
        {
            $value['text'] = trim(strip_tags($value['html']));
            return $value;
        });
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('html', $filtered['text']);
    }

    function testSelectorPath()
    {
        $this->filtrator->add('array[whitespace]', 'StringTrim');
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('   some string   ', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testFilterRecursivity()
    {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('some string', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testFilterRemoval()
    {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $this->filtrator->remove('*', 'StringTrim');
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('   some string   ', $filtered['whitespace']);
    }

    function testFilterPriority()
    {
        $this->filtrator->add('whitespace', 'StringTrim')
            ->add('whitespace', __NAMESPACE__ . '\postFiltrationFunction', null, false, - 1)
            ->add('whitespace', __NAMESPACE__ . '\preFiltrationFunction', null, false, - 1);
        $filtered = $this->filtrator->filter($this->data);
        $this->assertEquals('pre.   some string   .post', $filtered['whitespace']);
    }

    function testFilteringSingleValue()
    {
        $this->filtrator->add('*', 'StringTrim', null, true);
        $this->assertEquals(array(
            'some string'
        ), $this->filtrator->filter(array(
            '   some string   '
        )));
    }

    function testDuplicateCallbacksNotAllowed()
    {
        $this->filtrator->add('*', 'StringTrim', null, true);
        $this->filtrator->add('*', 'StringTrim', null, true);
        
        $this->assertEquals(1, count($this->filtrator->getAll()['*']));
    }

    function testExceptionThrownForUncallableFilters()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->filtrator->add('*', 'hopefully_this_is_not_a_valid_function');
    }

    function testExceptionThrownForInvalidFilterOptions()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->filtrator->add('*', 'StringTrim', new \stdClass());
    }

    function testFiltersMap()
    {
        $this->filtrator->registerFilterClass('removewhitespace', '\Sirius\Filtration\Filter\StringTrim');
        $this->filtrator->add('trim', 'removewhitespace');
        $this->assertEquals(array(
            'trim' => 'abc',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }

    function testCustomFilterClass() {
        $this->filtrator->add('real', '\Sirius\Filtration\FakeFilter');
        $this->assertEquals(array(
        	'real' => 'fake'
        ), $this->filtrator->filter(array(
        	'real' => 'real'
        ))); 
    }
    
    function testAddingObjectsAsFilters() {
        $this->filtrator->add('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->assertEquals(array(
            'trim' => 'abc',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }
    
    function testExceptionThrownIfFilterCannotBeConstructed() {
        $this->setExpectedException('\InvalidArgumentException');
        $this->filtrator->add('trim', new \stdClass());
    }
    
    function testFilteringNonArrays() {
        $obj = new \stdClass();
        $obj->key = 'value';
        $this->assertEquals($obj, $this->filtrator->filter($obj));
    }
    
    function testRemovingAllFiltersForASelector()
    {
        $this->filtrator->add('whitespace', __NAMESPACE__ . '\postFiltrationFunction')->add('whitespace', __NAMESPACE__ . '\preFiltrationFunction');
        $this->filtrator->remove('whitespace', true);
        $this->assertEquals(array(), $this->filtrator->getAll());
    }
    
    function testRemovingObjectsAsFilters() {
        $this->filtrator->add('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->filtrator->remove('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->assertEquals(array(
            'trim' => '  abc  ',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }
    
    function testFilterOptions() {
        $this->filtrator->add('text', 'stringtrim', '{"side": "right"}');
        $this->filtrator->add('another_text', 'stringtrim', "side=left");
        $this->assertEquals(array(
        	'text' => '  abc',
            'another_text' => 'abc   '
        ), $this->filtrator->filter(array(
        	'text' => '  abc   ',
            'another_text'=> '   abc   '
        )));
    }
}