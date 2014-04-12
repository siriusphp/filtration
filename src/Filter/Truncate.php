<?php
namespace Sirius\Filtration\Filter;

class Truncate extends AbstractFilter
{

    const OPTION_LIMIT = 'limit';

    const OPTION_ELLIPSIS = 'ellipsis';

    const OPTION_BREAK_WORDS = 'break_words';

    protected $options = array(
        self::OPTION_LIMIT => false,
        self::OPTION_BREAK_WORDS => true,
        self::OPTION_ELLIPSIS => '...'
    );

    function filterSingle($value, $valueIdentifier = null)
    {
        // not a string, move along
        if (! is_string($value)) {
            return $value;
        }

        if (! $this->options[self::OPTION_LIMIT] || strlen($value) <= $this->options[self::OPTION_LIMIT]) {
            return $value;
        }

        $limit = $this->options[self::OPTION_LIMIT];
        $firstSpace = strpos($value, ' ');

        // in case word breaking is not allowed find the previous space
        if (!$this->options[self::OPTION_BREAK_WORDS]) {
            if ($firstSpace === false) {
                $limit = strlen($value);
            } else {
                $limit = max($firstSpace, min($limit, (int)strrpos(substr($value, 0, $limit), ' ')));
            }
        }

        $isWordBreaker = false;
        if (substr($value, $limit, 1) != ' '
            && substr($value, $limit + 1, 1) != ' ') {
            $isWordBreaker = true;
        }

        $truncated = rtrim(substr($value, 0, $limit));

        if ($this->options[self::OPTION_ELLIPSIS]
            && $truncated != $value) {
            $truncated .= $this->options[self::OPTION_ELLIPSIS];
        }

        return $truncated;
    }
}
