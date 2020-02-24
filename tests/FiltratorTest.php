<?php

namespace Sirius\Filtration;

use PHPUnit\Framework\TestCase;

function postFiltrationFunction($value)
{
    return $value . '.post';
}

function preFiltrationFunction($value)
{
    return 'pre.' . $value;
}

class FakeFilter extends Filter\AbstractFilter
{

    function filterSingle($value, $valueIdentifier = null)
    {
        return 'fake';
    }
}

class FiltratorTest extends TestCase
{

    protected function setUp(): void
    {
        $this->filterFactory = new FilterFactory();
        $this->filtrator     = new Filtrator($this->filterFactory);
        $this->sampleData    = array(
            'whitespace' => '   some string   ',
            'html'       => '   <strong><em>html</em></strong>',
            'array'      => array(
                'whitespace' => '   some string   ',
                'array'      => array(
                    'whitespace' => '  some string   '
                )
            )
        );
    }

    function testRootFilters()
    {
        $this->filtrator->add('/', function ($value) {
            $value['text'] = trim(strip_tags($value['html']));

            return $value;
        });
        $filtered = $this->filtrator->filter($this->sampleData);
        $this->assertEquals('html', $filtered['text']);
    }

    function testSelectorPath()
    {
        $this->filtrator->add('whitespace', 'StringTrim');
        $this->filtrator->add('array[whitespace]', 'StringTrim');
        $filtered = $this->filtrator->filter($this->sampleData);
        $this->assertEquals('some string', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testFilterRecursivity()
    {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $filtered = $this->filtrator->filter($this->sampleData);
        $this->assertEquals('some string', $filtered['whitespace']);
        $this->assertEquals('some string', $filtered['array']['whitespace']);
    }

    function testAllowedSelectors()
    {
        $this->filtrator->add('array[whitespace]', 'StringTrim');
        $this->filtrator->setAllowed(['whitespace']);
        $filtered = $this->filtrator->filter($this->sampleData);
        $this->assertTrue(isset($filtered['whitespace']));
        $this->assertTrue(isset($filtered['array']['whitespace']));
        $this->assertFalse(isset($filtered['html']));
        $this->assertFalse(isset($filtered['array']['array']));
    }

    function testFilterRemoval()
    {
        $this->filtrator->add('*', 'StringTrim', null, true, 0);
        $this->filtrator->remove('*', 'StringTrim');
        $filtered = $this->filtrator->filter($this->sampleData);
        $this->assertEquals('   some string   ', $filtered['whitespace']);
    }

    function testFilterPriority()
    {
        $this->filtrator->add('whitespace', 'StringTrim')
                        ->add('whitespace', __NAMESPACE__ . '\postFiltrationFunction', null, false, -1)
                        ->add('whitespace', __NAMESPACE__ . '\preFiltrationFunction', null, false, -1);
        $filtered = $this->filtrator->filter($this->sampleData);
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

    function testExceptionThrownForUncallableFilters()
    {
        $this->expectException('\InvalidArgumentException');
        $this->filtrator->add('*', 'hopefully_this_is_not_a_valid_function');
    }

    function testExceptionThrownForInvalidFilterOptions()
    {
        $this->expectException('\InvalidArgumentException');
        $this->filtrator->add('*', 'StringTrim', new \stdClass());
    }

    function testFiltersMap()
    {
        $this->filterFactory->registerFilter('removewhitespace', '\Sirius\Filtration\Filter\StringTrim');
        $this->filtrator->add('trim', 'removewhitespace');
        $this->assertEquals(array(
            'trim' => 'abc',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }

    function testCustomFilterClass()
    {
        $this->filtrator->add('real', '\Sirius\Filtration\FakeFilter');
        $this->assertEquals(array(
            'real' => 'fake'
        ), $this->filtrator->filter(array(
            'real' => 'real'
        )));
    }

    function testAddingObjectsAsFilters()
    {
        $this->filtrator->add('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->assertEquals(array(
            'trim' => 'abc',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }

    function testExceptionThrownIfFilterCannotBeConstructed()
    {
        $this->expectException('\InvalidArgumentException');
        $this->filtrator->add('trim', new \stdClass());
    }

    function testRemovingAllFiltersForASelector()
    {
        $this->filtrator->add('whitespace', __NAMESPACE__ . '\postFiltrationFunction')->add('whitespace', __NAMESPACE__ . '\preFiltrationFunction');
        $this->filtrator->remove('whitespace', true);
        $this->assertEquals(array(), $this->filtrator->getFilters());
    }

    function testRemovingObjectsAsFilters()
    {
        $this->filtrator->add('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->filtrator->remove('trim', new \Sirius\Filtration\Filter\StringTrim());
        $this->assertEquals(array(
            'trim' => '  abc  ',
        ), $this->filtrator->filter(array(
            'trim' => '  abc  ',
        )));
    }

    function testFilterOptions()
    {
        $this->filtrator->add('text', 'stringtrim', '{"side": "right"}');
        $this->filtrator->add('another_text', 'stringtrim', "side=left");
        $this->assertEquals(array(
            'text'         => '  abc',
            'another_text' => 'abc   '
        ), $this->filtrator->filter(array(
            'text'         => '  abc   ',
            'another_text' => '   abc   '
        )));
    }

    function testExceptionThrownOnInvalidSelector()
    {
        $this->expectException('\InvalidArgumentException');
        $this->filtrator->add(new \stdClass());
    }

    function testAddingMultipleRulesAtOnce()
    {
        $this->filtrator->add(array(
            'text'         => array(array('stringtrim', '{"side": "right"}')),
            'another_text' => array(array('stringtrim', 'side=left'))
        ));
        $this->assertEquals(array(
            'text'         => '  abc',
            'another_text' => 'abc   '
        ), $this->filtrator->filter(array(
            'text'         => '  abc   ',
            'another_text' => '   abc   '
        )));
    }

    function testAddingMultipleRulesAsArrayPerSelectorAtOnce()
    {
        $this->filtrator->add('text', array('stringtrim', 'truncate(limit=10)(true)(10)'));
        $this->assertEquals(array(
            'text' => 'A text tha...'
        ), $this->filtrator->filter(array(
            'text' => '     A text that is more than 10 characters long'
        )));
    }


    function testAddingMultipleRulesAsStringPerSelectorAtOnce()
    {
        $this->filtrator->add('text', 'stringtrim | truncate(limit=10)(true)(10)');
        $this->assertEquals(array(
            'text' => 'A text tha...'
        ), $this->filtrator->filter(array(
            'text' => '     A text that is more than 10 characters long'
        )));
    }

}
