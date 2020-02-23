<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class NormalizeDate extends AbstractFilter
{
    const OPTION_INPUT_FORMAT = 'input_format';

    const OPTION_OUTPUT_FORMAT = 'output_format';

    protected $options = array(
        self::OPTION_INPUT_FORMAT => 'd/m/Y',
        self::OPTION_OUTPUT_FORMAT => 'Y-m-d'
    );

    public function filterSingle($value, $valueIdentifier = null)
    {
        $value = (string) $value;
        $timestamp = $this->parseDateFromString($value, $this->options['input_format']);
        return date($this->options['output_format'], $timestamp);
    }

    protected function parseDateFromString($string, $format)
    {
        $result = date_parse_from_format($format, $string);
        return mktime(
            (int) $result['hour'],
            (int) $result['minute'],
            (int) $result['second'],
            (int) $result['month'],
            (int) $result['day'],
            (int) $result['year']
        );
    }
}
