<?php
declare(strict_types=1);

namespace Sirius\Filtration;

use Sirius\Filtration\Filter\AbstractFilter as AbstractFilterAlias;

class FilterSet extends \SplPriorityQueue
{
    /**
     * Cache of priorities that were already taken
     *
     * @var array
     */
    protected $allocatedPriorities = [];

    public function __construct()
    {
        $this->setExtractFlags(static::EXTR_BOTH);
    }

    public function compare($priority1, $priority2)
    {
        if ($priority1 === $priority2) {
            return 0;
        }
        return $priority1 < $priority2 ? 1 : -1;
    }

    public function insert($filter, $priority)
    {
        if (!$filter instanceof Filter\AbstractFilter) {
            throw new \InvalidArgumentException('Only filter instances can be added to the filter set');
        }
        $this->allocatedPriorities[] =  $this->getValidPriority($priority);
        return parent::insert($filter, $priority);
    }

    public function remove($filter)
    {
        /* @var $filter AbstractFilterAlias */
        if (!$filter instanceof Filter\AbstractFilter) {
            throw new \InvalidArgumentException('Only filter instances can be removed from the filter set');
        }
        $filters = [];
        $this->top();
        while ($this->valid()) {
            $item = $this->current();
            /* @var $itemFilter AbstractFilterAlias */
            $itemFilter = $item['data'];
            if ($itemFilter->getUniqueId() !== $filter->getUniqueId()) {
                $filters[$item['priority']] = $item['data'];
            }
            $this->next();
        }
        foreach ($filters as $priority => $filter) {
            $this->insert($filter, $priority);
        }
        return $this;
    }

    /**
     * Get a valid priority number to attach to a filter
     *
     * @param int $desiredPriority
     * @return number
     */
    protected function getValidPriority($desiredPriority)
    {
        // make sure the priority is an integer so we don't screw up the math
        // also multiply everything by 10000 because the priority must be an integer
        // as it will be used as the $filters[$selector] array key
        $desiredPriority = (int) $desiredPriority * 10000;
        if (! in_array($desiredPriority, $this->allocatedPriorities)) {
            return $desiredPriority;
        }
        // the increment will be used to determine when we find an available spot
        // obviously if you have 10000 filters with priority 0,
        // the 10000th will get to priority one but that's a chance we are willing to take
        $increment = 1;
        while (in_array($desiredPriority, $this->allocatedPriorities)) {
            $desiredPriority += $increment;
        }
        return $desiredPriority;
    }

    public function applyFilters($value, $valueIdentifier = null, $context = null)
    {
        foreach (clone $this as $filter) {
            if (is_array($filter)) {
                $filter = $filter['data'];
            }
            /* @var $filter AbstractFilterAlias */
            $filter->setContext($context);
            $value = $filter->filter($value, (string) $valueIdentifier);
        }
        return $value;
    }
}
