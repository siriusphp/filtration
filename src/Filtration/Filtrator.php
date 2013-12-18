<?php
namespace Sirius\Filtration;

use Sirius\Filtration\Utils;

class Filtrator
{
    // selector to specify that the filter is applied to the entire data set
    const SELECTOR_ROOT = '/';
    
    // selector to specify that the filter is applied to all ITEMS of a set
    const SELECTOR_ANY = '*';

    protected $filtersMap = array(
        'callback' => '\Sirius\Filtration\Filter\Callback',
        'cleanarray' => '\Sirius\Filtration\Filter\CleanArray',
        'double' => '\Sirius\Filtration\Filter\Double',
        'integer' => '\Sirius\Filtration\Filter\Integer',
        'normalizedate' => '\Sirius\Filtration\Filter\NormalizeDate',
        'normalizenumber' => '\Sirius\Filtration\Filter\NormalizeNumber',
        'nullify' => '\Sirius\Filtration\Filter\Nullify',
        'obfuscate' => '\Sirius\Filtration\Filter\Obfuscate',
        'stringtrim' => '\Sirius\Filtration\Filter\StringTrim'
    );

    /**
     * The list of filters available in the filtrator
     *
     * @var array
     */
    protected $filters = array();

    function __construct($filters = array())
    {}

    function registerFilterClass($name, $class)
    {
        if ($class && class_exists($class) && is_subclass_of($class, '\Sirius\Filtration\Filter\AbstractFilter')) {
            $this->filtersMap[$name] = $class;
        }
        return $this;
    }

    /**
     * Get a valid priority number to attach to a filter
     *
     * @param string $selector            
     * @param int $desiredPriority            
     * @return number
     */
    protected function getValidPriority($selector, $desiredPriority)
    {
        // make sure the priority is an integer so we don't screw up the math
        // also multiply everything by 10000 because the priority must be an integer
        // as it will be used as the $filters[$selector] array key
        $desiredPriority = (int) $desiredPriority * 10000;
        if (! array_key_exists($selector, $this->filters)) {
            return $desiredPriority;
        }
        // the increment will be used to determine when we find an available spot
        // obviously if you have 10000 filters with priority 0,
        // the 10000th will get to priority one but that's a chance we are willing to take
        $increment = 1;
        while (array_key_exists($desiredPriority, $this->filters[$selector])) {
            $desiredPriority += $increment;
        }
        return $desiredPriority;
    }

    /**
     * Add a filter to the filters stack
     *
     * @example // normal callback
     *          $filtrator->add('title', '\strip_tags');
     *          // anonymous function
     *          $filtrator->add('title', function($value){ return $value . '!!!'; });
     *          // filter class from the library
     *          $filtrator->add('title', 'normalizedate', array('format' => 'm/d/Y'));
     *          // custom class
     *          $filtrator->add('title', '\MyApp\Filters\CustomFilter');
     * @param string $selector            
     * @param
     *            callable|filter class name|\Sirius\Filtration\Filter\AbstractFilter $callbackOrFilterName
     * @param string|array $params            
     * @param bool $recursive            
     * @param number $priority            
     * @return \Sirius\Filtration\Filtrator
     */
    function add($selector, $callbackOrFilterName, $options = null, $recursive = false, $priority = 0)
    {
        $filter = $this->createFilter($callbackOrFilterName, $options, $recursive);
        if (! $this->has($selector, $filter)) {
            $priority = $this->getValidPriority($selector, $priority);
            if (! array_key_exists($selector, $this->filters)) {
                $this->filters[$selector] = array();
            }
            $this->filters[$selector][$priority] = $filter;
            ksort($this->filters[$selector]);
        }
        return $this;
    }

    /**
     * Factory method to create a filter from various options
     *
     * @param callable|class|filter $callbackOrFilterName            
     * @param string|array $options            
     * @param bool $resursive            
     * @throws \InvalidArgumentException
     * @return \Sirius\Filtration\Filter\AbstractFilter
     */
    protected function createFilter($callbackOrFilterName, $options = null, $resursive = false)
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
            $filter = new \Sirius\Filtration\Filter\Callback(array(
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
        } elseif (is_object($callbackOrFilterName) && $callbackOrFilterName instanceof \Sirius\Filtration\Filter\AbstractFilter) {
            $filter = $callbackOrFilterName;
        }
        if (! isset($filter)) {
            throw new \InvalidArgumentException('Invalid value for the $callbackorFilterName parameter');
        }
        return $filter;
    }

    /**
     * Remove a filter from the stack
     *
     * @param string $selector            
     * @param callable|classname|filter|TRUE $callbackOrName            
     * @return \Sirius\Filtration\Filtrator
     */
    function remove($selector, $callbackOrName = true)
    {
        if (array_key_exists($selector, $this->filters)) {
            if ($callbackOrName === true) {
                unset($this->filters[$selector]);
            } else {
                if (! is_object($callbackOrName)) {
                    $filter = $this->createFilter($callbackOrName);
                } else {
                    $filter = $callbackOrName;
                }
                foreach ($this->filters[$selector] as $priority => $f) {
                    if ($f->getUniqueId() === $filter->getUniqueId()) {
                        unset($this->filters[$selector][$priority]);
                        break;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Check if a filter is in the stack
     *
     * @param string $selector            
     * @param Sirius\Filtration\Filter\AbstractFilter $filter            
     * @return boolean
     */
    function has($selector, $filter)
    {
        if (array_key_exists($selector, $this->filters)) {
            foreach ($this->filters[$selector] as $f) {
                if ($f->getUniqueId() === $filter->getUniqueId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve all filters stack
     *
     * @return array
     */
    function getAll()
    {
        return $this->filters;
    }

    /**
     * Apply filters to an array
     *
     * @param array $data            
     * @return array
     */
    function filter($data = array())
    {
        if (! is_array($data)) {
            return $data;
        }
        // first apply the filters to the ROOT
        if (isset($this->filters[self::SELECTOR_ROOT])) {
            foreach ($this->filters[self::SELECTOR_ROOT] as $filter) {
                $data = $filter->filter($data);
            }
        }
        foreach ($data as $key => $value) {
            $data[$key] = $this->filterItem($data, $key);
        }
        return $data;
    }

    /**
     * Apply filters on a single item in the array
     *
     * @param array $data            
     * @param string $valueIdentifier            
     * @return mixed
     */
    function filterItem($data, $valueIdentifier)
    {
        $value = Utils::arrayGetByPath($data, $valueIdentifier);
        $value = $this->applyFilters($value, $valueIdentifier);
        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $value[$k] = $this->filterItem($data, "{$valueIdentifier}[{$k}]");
            }
        }
        return $value;
    }

    /**
     * Apply filters to a single value
     *
     * @param mixed $value
     *            value of the item
     * @param string $valueIdentifier
     *            array element path (eg: 'key' or 'key[0][subkey]')
     * @return mixed
     */
    function applyFilters($value, $valueIdentifier)
    {
        foreach ($this->filters as $selector => $filters) {
            if ($selector != self::SELECTOR_ROOT && $this->itemMatchesSelector($valueIdentifier, $selector)) {
                foreach ($filters as $filter) {
                    $value = $filter->filter($value, $valueIdentifier);
                }
            }
        }
        return $value;
    }

    /**
     * Checks if an item matches a selector
     *
     * @example $this->('key[subkey]', 'key[*]') -> true;
     *          $this->('key[subkey]', 'subkey') -> false;
     *         
     * @param string $item            
     * @param string $selector            
     * @return boolean number
     */
    protected function itemMatchesSelector($item, $selector)
    {
        // the selector is a simple path identifier
        // NOT something like key[*][subkey]
        if (strpos($selector, '*') === false) {
            return $item === $selector;
        }
        $regex = '/' . str_replace('*', '[^\]]+', str_replace(array(
            '[',
            ']'
        ), array(
            '\[',
            '\]'
        ), $selector)) . '/';
        return preg_match($regex, $item);
    }
}
