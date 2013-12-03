<?php

namespace Sirius\Filtration\Filter;

class StringTrim extends AbstractFilter {
    const OPTION_CHARACTERS = 'characters';
    const OPTION_SIDE = 'side';
    const OPTION_SIDE_LEFT = 'left';
    const OPTION_SIDE_RIGHT = 'right';
    const OPTION_SIDE_BOTH = 'both';
    
    protected $options = array(
    	self::OPTION_SIDE => self::OPTION_SIDE_BOTH,
        self::OPTION_CHARACTERS => " \n\r\t"
    );
    
    function filter($value, $valueIdentifier = null) {
        $function = '\trim';
        if ($this->options['side'] == 'left') {
            $function = '\ltrim';
        } else if ($this->options['side'] == 'right') {
            $function = '\rtrim';
        }
        $value = call_user_func($function, $value, $this->options['characters']);
        return $value;
    }
}