<?php

namespace Sirius\Filtration\Filter;

class Integer extends AbstractFilter {
    
    function filterSingle($value, $valueIdentifier = null) {
        if (is_object($value)) {
            return $value;
        }
        if ($value == 0) {
            return 0;
        }
        $value = floatval($value);
        return (int)$value;
    }
}