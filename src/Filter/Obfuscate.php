<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class Obfuscate extends AbstractFilter
{
    // how may characters from the benining are left untouched
    const OPTION_START_CHARACTERS = 'start_characters';
    // how may characters from the end are left untouched
    const OPTION_END_CHARACTERS = 'end_characters';
    // replacement character
    const OPTION_REPLACEMENT_CHAR = 'replacement_char';

    protected $options = [
        self::OPTION_START_CHARACTERS => 0,
        self::OPTION_END_CHARACTERS => 0,
        self::OPTION_REPLACEMENT_CHAR => '*'
    ];

    public function filterSingle($value, string $valueIdentifier = null)
    {
        // not a string, move along
        if (! is_string($value)) {
            return $value;
        }
        
        $len = strlen($value);
        $start = $this->options[self::OPTION_START_CHARACTERS] ?
            substr($value, 0, $this->options[self::OPTION_START_CHARACTERS]) :
            '';
        $end = $this->options[self::OPTION_END_CHARACTERS] ?
            substr($value, $len - $this->options[self::OPTION_END_CHARACTERS]) :
            '';
        $middle = str_repeat(
            $this->options[self::OPTION_REPLACEMENT_CHAR],
            $len - $this->options[self::OPTION_START_CHARACTERS] - $this->options[self::OPTION_END_CHARACTERS]
        );
        return $start . $middle . $end;
    }
}
