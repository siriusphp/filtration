<?php
namespace Sirius\Filtration\Filter;

class Censor extends AbstractFilter
{
    // how may characters from the benining are left untouched
    const OPTION_START_CHARACTERS = 'start_characters';
    // how may characters from the end are left untouched
    const OPTION_END_CHARACTERS = 'end_characters';
    // replacement character
    const OPTION_REPLACEMENT_CHAR = 'replacement_char';
    // censored words
    const OPTION_WORDS = 'words';

    protected $options = array(
        self::OPTION_START_CHARACTERS => 1,
        self::OPTION_END_CHARACTERS => 1,
        self::OPTION_REPLACEMENT_CHAR => '*',
        self::OPTION_WORDS => array('fuck', 'fucker', 'fuckers', 'fucking', 'motherfucker', 'asshole', 'cunt', 'dick', 'nigger', 'faggot')
    );
    
    protected $obfuscator;

    function setOption($name, $value) {
        parent::setOption($name, $value);
        // reset the obfuscator in case the options are changed during the usage
        $this->obfuscator = null;
        return $this;
    }
    
    protected function getReplaceCallback() {
        if (!$this->obfuscator) {
            $this->obfuscator = new Obfuscate($this->options);
        }
        $obfuscator = $this->obfuscator;
        return function($matches) use ($obfuscator) {
        	return $obfuscator->filter($matches[0]);
        };
    }
    
    function filterSingle($value, $valueIdentifier = null)
    {
        foreach ($this->options[self::OPTION_WORDS] as $word) {
            $value = \preg_replace_callback("|\b{$word}\b|i", $this->getReplaceCallback(), $value);
        }
        
        return $value;
    }
}