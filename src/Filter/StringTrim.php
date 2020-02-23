<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class StringTrim extends AbstractFilter
{
    const OPTION_CHARACTERS = 'characters';

    const OPTION_SIDE = 'side';

    const VALUE_SIDE_LEFT = 'left';

    const VALUE_SIDE_RIGHT = 'right';

    const VALUE_SIDE_BOTH = 'both';

    protected $options = array(
        self::OPTION_SIDE => self::VALUE_SIDE_BOTH,
        self::OPTION_CHARACTERS => " \n\r\t"
    );

    public function filterSingle($value, $valueIdentifier = null)
    {
        // not a string, move along
        if (! is_string($value)) {
            return $value;
        }
        
        $function = '\trim';
        if ($this->options['side'] == 'left') {
            $function = '\ltrim';
        } elseif ($this->options['side'] == 'right') {
            $function = '\rtrim';
        }
        return call_user_func($function, $value, $this->options['characters']);
    }
}
