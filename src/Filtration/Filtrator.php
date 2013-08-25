<?php

namespace Sirius\Filtration;

class Filtrator {
	protected $filters = array();

	function __construct($filters = array()) {

	}

    protected function getValidPriority($selector, $desiredPriority) {
        // make sure the priority is an integer so we don't screw up the math
        $desiredPriority = (int) $desiredPriority;
        if (!array_key_exists($selector, $this->rules)) {
            return $desiredPriority;
        }
        // the increment will be used to determine when we find an available spot
        // obviously if you have 10000 filters with priority 0, 
        // the 10000th will get to priority one but that's a chance we are willing to take
        $increment = 1 / 10000; 
        while (array_key_exists($desiredPriority, $this->filters[$selector])) {
            $desiredPriority += $increment;
        }
        return $desiredPriority;
    }

	function add($selector, $filter, $params = array(), $priority = 0, $recursive = false) {
        if (!is_callable($fitler)) {
            throw new \InvalidArgumentException('The filter is not callable');
        }
        if (!$this->has($selector, $filter)) {
            if (!array_key_exists($selector, $this->filters)) {
                $this->filters[$selector] = array();
            }
            $priority = $this->getValidPriority($selector, $priority);
            $this->filters[$selector][$priority] = array(
                'selector'  => $selector,
                'filter'    => $fitler,
                'params'    => $params,
                'recursive' => $recursive
            );
            ksort($this->filters[$selector]);
        }
        return $this;
	}

	function remove($selector, $filter = true) {
        if (array_key_exists)
        return $this;
	}

	function has($selector, $filter) {

	}

    function getBySelector($selector) {

    }

	function getAll() {
        return $this->filters;
	}

    function apply($data = array()) {
        if (!is_array($data)) {
            return $data;
        }
    }

}