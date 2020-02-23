<?php

namespace Sirius\Filtration\Filter;

class FakeFilter extends AbstractFilter
{

    function filterSingle($value, $valueIdentifier = null)
    {
        return 'fake';
    }
}