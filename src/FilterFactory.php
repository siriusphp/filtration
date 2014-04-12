<?php

namespace Sirius\Filtration;

use Sirius\Filtration\Filter\AbstractFilter;
use Sirius\Filtration\Filter\Callback;

class FilterFactory {

    protected $filtersMap = array(
        'callback' => '\Sirius\Filtration\Filter\Callback',
        'censor' => '\Sirius\Filtration\Filter\Censor',
        'cleanarray' => '\Sirius\Filtration\Filter\CleanArray',
        'double' => '\Sirius\Filtration\Filter\Double',
        'integer' => '\Sirius\Filtration\Filter\Integer',
        'normalizedate' => '\Sirius\Filtration\Filter\NormalizeDate',
        'normalizenumber' => '\Sirius\Filtration\Filter\NormalizeNumber',
        'nullify' => '\Sirius\Filtration\Filter\Nullify',
        'obfuscate' => '\Sirius\Filtration\Filter\Obfuscate',
        'stringtrim' => '\Sirius\Filtration\Filter\StringTrim',
        'trim' => '\Sirius\Filtration\Filter\StringTrim',
        'truncate' => '\Sirius\Filtration\Filter\Truncate'
    );

    function registerFilter($name, $class)
    {
        if ($class && class_exists($class) && is_subclass_of($class, '\Sirius\Filtration\Filter\AbstractFilter')) {
            $this->filtersMap[$name] = $class;
        }
        return $this;
    }


    /**
     * Factory method to create a filter from various options
     *
     * @param callable|string $callbackOrFilterName
     * @param string|array $options
     * @param bool $resursive
     * @throws \InvalidArgumentException
     * @return AbstractFilter
     */
    function createFilter($callbackOrFilterName, $options = null, $resursive = false)
    {
        if ($options && is_string($options)) {
            $startChar = substr($options, 0, 1);
            if ($startChar == '{' || $startChar == '[') {
                $options = json_decode($options, true);
            } else {
                parse_str($options, $output);
                $options = $output;
            }
        } elseif (! $options) {
            $options = array();
        }

        if (! is_array($options)) {
            throw new \InvalidArgumentException('Validator options should be an array, JSON string or query string');
        }

        if (is_callable($callbackOrFilterName)) {
            $filter = new Callback(array(
                'callback' => $callbackOrFilterName,
                'arguments' => $options
            ), $resursive);
        } elseif (is_string($callbackOrFilterName)) {
            if (class_exists('\Sirius\Filtration\Filter\\' . $callbackOrFilterName)) {
                $callbackOrFilterName = '\Sirius\Filtration\Filter\\' . $callbackOrFilterName;
            }
            // use the validator map
            if (isset($this->filtersMap[strtolower($callbackOrFilterName)])) {
                $callbackOrFilterName = $this->filtersMap[strtolower($callbackOrFilterName)];
            }
            if (class_exists($callbackOrFilterName) && is_subclass_of($callbackOrFilterName, '\Sirius\Filtration\Filter\AbstractFilter')) {
                $filter = new $callbackOrFilterName($options, $resursive);
            } else {
                throw new \InvalidArgumentException(sprintf('Impossible to determine the filter based on the name %s', (string) $callbackOrFilterName));
            }
        } elseif (is_object($callbackOrFilterName) && $callbackOrFilterName instanceof AbstractFilter) {
            $filter = $callbackOrFilterName;
        }
        if (! isset($filter)) {
            throw new \InvalidArgumentException('Invalid value for the $callbackorFilterName parameter');
        }
        return $filter;
    }

}
