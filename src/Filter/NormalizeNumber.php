<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class NormalizeNumber extends AbstractFilter
{
    const OPTION_THOUSANDS_SEPARATOR = 'thousands_separator';

    const OPTION_DECIMAL_POINT = 'decimal_point';

    const VALUE_POINT = '.';

    const VALUE_COMMA = ',';

    protected $options = array(
        self::OPTION_THOUSANDS_SEPARATOR => self::VALUE_POINT,
        self::OPTION_DECIMAL_POINT => self::VALUE_COMMA
    );

    public function filterSingle($value, $valueIdentifier = null)
    {
        $value = (string) $value;
        // number is already normalized
        $floatedValue =\floatval($value);
        
        // check for the string length because:
        // 1. floatval('12.456,67') returns 12.456 and
        // 2. PHP returns true for '12.456,67' == 12.456
        if ($floatedValue == $value && strlen((string) $floatedValue) == strlen($value)) {
            return $floatedValue;
        }
        
        // attempt to normalize it:
        // remove spaces and thousands separator
        // replace local decimal point with .
        $value = strtr($value, array(
            ' ' => '',
            $this->options[self::OPTION_THOUSANDS_SEPARATOR] => '',
            $this->options[self::OPTION_DECIMAL_POINT] => '.'
        ));
        return\floatval($value);
    }
}
