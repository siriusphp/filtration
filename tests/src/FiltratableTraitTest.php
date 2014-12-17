<?php
namespace Sirius\Filtration;

use Sirius\Filtration\Filtrator;

class FiltratableForm {
    use \Sirius\Filtration\FiltratableTrait;
    
    protected $values = array();
    
    function setValues($values) {
        $values = $this->getFiltrator()->filter($values);
        $this->values = $values;
        return $this;
    }
    
    function getValues() {
        return $this->values;
    }
}

class FiltratableTraitTest extends \PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->form = new FiltratableForm();
        $this->form->getFiltrator()->add(array(
        	'title' => 'stringtrim | nullify | truncate(limit=10)',
            'description' => 'stringtrim | nullify'
        ));
    }

    function testFilter()
    {
        $this->form->setValues(array(
        	'title' => 'Title is very long',
            'description' => '    '
        ));
        $this->assertEquals(array(
        	'title' => 'Title is v...',
            'description' => null
        ), $this->form->getValues());
    }
    
    function testSettingNewFiltrator() {
        $filtrator = new Filtrator();
        $filtrator->add(array(
            'title' => 'stringtrim | nullify | truncate(limit=10)',
        ));
        $this->form->setFiltrator($filtrator);
        $this->form->setValues(array(
        	'title' => 'Title is very long',
            'description' => '    '
        ));
        $this->assertEquals(array(
        	'title' => 'Title is v...',
            'description' => '    '
        ), $this->form->getValues());
    }
}