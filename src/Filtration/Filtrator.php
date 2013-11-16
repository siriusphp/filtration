<?php

namespace Sirius\Filtration;

use Sirius\Filtration\Utils;

class Filtrator {
	// selector to specify that the filter is applied to the entire data set
	const SELECTOR_ROOT = '/';
	
	// selector to specify that the filter is applied to all ITEMS of a set
	const SELECTOR_ANY = '*';
	
	/**
	 * The list of filters available in the filtrator
	 * 
	 * @var array
	 */
	protected $filters = array();

	function __construct($filters = array()) {

	}

    /**
     * Get a valid priority number to attach to a filter
     * 
     * @param string $selector
     * @param int $desiredPriority
     * @return number
     */
    protected function getValidPriority($selector, $desiredPriority) {
        // make sure the priority is an integer so we don't screw up the math
        // also multiply everything by 10000 because the priority must be an integer
        // as it will be used as the $filters[$selector] array key
        $desiredPriority = (int) $desiredPriority * 10000;
        if (!array_key_exists($selector, $this->filters)) {
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
	 * Add a filter to the list of stack
	 * 
	 * @param string $selector
	 * @param callable $callback
	 * @param array $params
	 * @param bool $recursive
	 * @param number $priority
	 * @throws \InvalidArgumentException
	 * @return \Sirius\Filtration\Filtrator
	 */
	function add($selector, $callback, $params = array(), $recursive = false, $priority = 0) {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The filter is not callable');
        }
        if (!$this->has($selector, $callback)) {
            $priority = $this->getValidPriority($selector, $priority);
            if (!array_key_exists($selector, $this->filters)) {
                $this->filters[$selector] = array();
            }
            $this->filters[$selector][$priority] = array(
                'callback'  => $callback,
                'params'    => is_array($params) ? $params : array(),
                'recursive' => $recursive
            );
            ksort($this->filters[$selector]);
        }
        return $this;
	}

	/**
	 * Remove a filter from the stack 
	 * 
	 * @param string $selector
	 * @param callable|true $callback
	 * @return \Sirius\Filtration\Filtrator
	 */
	function remove($selector, $callback = true) {
        if (array_key_exists($selector, $this->filters)) {
            if ($callback === true) {
                unset($this->filters[$selector]);
            } else {
                foreach ($this->filters[$selector] as $priority => $filter) {
                    if ($filter['callback'] === $callback) {
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
	 * @param callable $callback
	 * @return boolean
	 */
	function has($selector, $callback) {
        if (array_key_exists($selector, $this->filters)) {
            foreach ($this->filters[$selector] as $filter) {
                if ($filter['callback'] === $callback) {
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
	function getAll() {
        return $this->filters;
	}

    /**
     * Apply filters to an array
     * 
     * @param array $data
     * @return array
     */
    function filter($data = array()) {
        if (!is_array($data)) {
            return $data;
        }
        // first apply the filters to the ROOT
        if (isset($this->filters[self::SELECTOR_ROOT])) {
        	foreach ($this->filters[self::SELECTOR_ROOT] as $filter) {
        		$data = $this->applyFilterToValue($data, $filter);
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
     * @param string $item
     * @return mixed
     */
    function filterItem($data, $item) {
        $value = Utils::arrayGetByPath($data, $item);
        $value = $this->applyFilters($item, $value);
        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $value[$k] = $this->filterItem($data, "{$item}[{$k}]");
            }
        }
        return $value; 
    }

    /**
     * Apply filters to a single value
     * 
     * @param  string $item  array element path (eg: 'key' or 'key[0][subkey]')
     * @param  mixed $value value of the item
     * @return mixed
     */
    function applyFilters($item, $value) {
        foreach ($this->filters as $selector => $filters) {
            if ($selector != self::SELECTOR_ROOT
            	&& $this->itemMatchesSelector($item, $selector)) {
                foreach ($filters as $filter) {
                    $value = $this->applyFilterToValue($value, $filter);
                }
            }
        }
        return $value;
    }

    /**
     * Applies a filter on a value
     * 
     * @param mixed $value
     * @param array $filter a filter as is stored in the filters stack
     * @return mixed
     */
    protected function applyFilterToValue($value, $filter) {
        if ($filter['recursive'] and is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->applyFilterToValue($v, $filter);
            }
        } else {
            $params = $filter['params'];
            array_unshift($params, $value);
            $value = call_user_func_array($filter['callback'], $params);
        }
        return $value;
    }

    /**
     * Checks if an item matches a selector
     * 
     * @example
     * $this->('key[subkey]', 'key[*]') -> true;
     * $this->('key[subkey]', 'subkey') -> false;
     * 
     * @param string $item
     * @param string $selector
     * @return boolean|number
     */
    protected function itemMatchesSelector($item, $selector) {
        // the selector is a simple path identifier
        // NOT something like key[*][subkey]
        if (strpos($selector, '*') === false) {
            return $item === $selector;
        }
        $regex = '/' . str_replace('*', '[^\]]+', str_replace(array('[', ']'), array('\[', '\]'), $selector)) . '/';
        return preg_match($regex, $item);
    }

}