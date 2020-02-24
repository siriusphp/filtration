<?php
declare(strict_types=1);

namespace Sirius\Filtration;

use Sirius\Filtration\Filter\AbstractFilter;
use Sirius\Filtration\Filter\Callback;

class FilterFactory
{
    protected $filtersMap = [
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
    ];

    public function registerFilter($name, $class)
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
    public function createFilter($callbackOrFilterName, $options = null, $resursive = false): AbstractFilter
    {
        if (is_callable($callbackOrFilterName)) {
            return new Callback([
                'callback' => $callbackOrFilterName,
                'arguments' => $options
            ], $resursive);
        }

        if (is_string($callbackOrFilterName)) {
            if (class_exists('\Sirius\Filtration\Filter\\' . $callbackOrFilterName)) {
                $callbackOrFilterName = '\Sirius\Filtration\Filter\\' . $callbackOrFilterName;
            }
            // use the validator map
            if (isset($this->filtersMap[strtolower($callbackOrFilterName)])) {
                $callbackOrFilterName = $this->filtersMap[strtolower($callbackOrFilterName)];
            }

            if (class_exists($callbackOrFilterName) && is_subclass_of($callbackOrFilterName, AbstractFilter::class)) {
                return new $callbackOrFilterName($options, $resursive);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Impossible to determine the filter based on the name %s',
                    (string) $callbackOrFilterName
                ));
            }
        }

        if (is_object($callbackOrFilterName) && $callbackOrFilterName instanceof AbstractFilter) {
            return $callbackOrFilterName;
        }

        throw new \InvalidArgumentException('Invalid value for the $callbackorFilterName parameter');
    }
}
