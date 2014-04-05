<?php
namespace Sirius\Filtration\Filter;

class Double extends AbstractFilter
{

    const OPTION_PRECISION = 'precision';

    protected $options = array(
        self::OPTION_PRECISION => 2
    );

    function filterSingle($value, $valueIdentifier = null)
    {
        if (is_object($value)) {
            return $value;
        }
        if ($value == 0) {
            return 0;
        }
        $value = floatval($value);
        $multiplier = pow(10, (int) $this->options['precision']);
        return round($value * $multiplier) / $multiplier;
    }
}