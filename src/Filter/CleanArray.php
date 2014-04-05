<?php
namespace Sirius\Filtration\Filter;

class CleanArray extends AbstractFilter
{

    const OPTION_NULLIFY = 'nullify';

    protected $options = array(
        self::OPTION_NULLIFY => true
    );

    function filter($value, $valueIdentifier = null)
    {
        if (! is_array($value)) {
            return $value;
        }
        $result = array();
        if ($this->options['nullify']) {
            $nullifier = new Nullify();
        }
        $arrayIsAssociative = array_keys($value) === range(0, count($value) - 1);
        foreach ($value as $k => $v) {
            if (isset($nullifier)) {
                $v = $nullifier->filter($v);
            }
            if ($v !== null) {
                if ($arrayIsAssociative) {
                    $result[] = $v;
                } else {
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }

    function filterSingle($value, $valueIdentifier = null)
    {
        return $this->filter($value, $valueIdentifier);
    }
}