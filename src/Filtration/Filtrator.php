<?php

namespace Sirius\Filtration;

use Sirius\Filtration\Utils;

class Filtrator {
	protected $filters = array();

	function __construct($filters = array()) {

	}

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

	function getAll() {
        return $this->filters;
	}

    function filter($data = array()) {
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            $data[$key] = $this->filterItem($data, $key);
        }
        return $data;
    }

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
            if ($this->itemMatchesSelector($item, $selector)) {
                foreach ($filters as $filter) {
                    $value = $this->applyFilterToValue($value, $filter);
                }
            }
        }
        return $value;
    }

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