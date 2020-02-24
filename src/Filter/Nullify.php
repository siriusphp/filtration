<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class Nullify extends AbstractFilter
{
    const OPTION_EMPTY_STRING = 'empty_string';

    const OPTION_ZERO = 'zero';

    protected $options = [
        self::OPTION_EMPTY_STRING => true,
        self::OPTION_ZERO => true
    ];

    public function filterSingle($value, string $valueIdentifier = null)
    {
        if (is_string($value) && $value == '' && $this->options['empty_string']) {
            return null;
        } elseif (is_string($value) && $value == '0' && $this->options['zero']) {
            return null;
        } elseif (! is_string($value) && $value == 0 && $this->options['zero']) {
            return null;
        }
        return $value;
    }
}
