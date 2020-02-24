<?php
declare(strict_types=1);
namespace Sirius\Filtration;

class Filtrator implements FiltratorInterface
{
    // selector to specify that the filter is applied to the entire data set
    const SELECTOR_ROOT = '/';

    // selector to specify that the filter is applied to all ITEMS of a set
    const SELECTOR_ANY = '*';

    protected $filterFactory;

    /**
     * The list of filters available in the filtrator
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $allowedSelectors = [];

    /**
     * @var array
     */
    protected $compiledAllowedSelectors = [];

    public function __construct(FilterFactory $filterFactory = null)
    {
        if (!$filterFactory) {
            $filterFactory = new FilterFactory();
        }
        $this->filterFactory = $filterFactory;
    }

    public function setAllowed(array $allowedSelectors = [])
    {
        $this->allowedSelectors = $allowedSelectors;
    }

    /**
     * Add a filter to the filters stack
     *
     * @example // normal callback
     *          $filtrator->add('title', '\strip_tags');
     *          // anonymous function
     *          $filtrator->add('title', function($value){ return $value . '!!!'; });
     *          // filter class from the library registered on the $filtersMap
     *          $filtrator->add('title', 'normalizedate', ['format' => 'm/d/Y']);
     *          // custom class
     *          $filtrator->add('title', '\MyApp\Filters\CustomFilter');
     *          // multiple filters as once with different ways to pass options
     *          $filtrator->add('title', [
     *              ['truncate', 'limit=10', true, 10],
     *              ['censor', ['words' => ['idiot']]
     *          ]);
     *          // multiple fitlers as a single string
     *          $filtrator->add('title', 'stringtrim(side=left)(true)(10) | truncate(limit=100)');
     * @param string|array $selector
     * @param mixed $callbackOrFilterName
     * @param array|null $options
     * @param bool $recursive
     * @param integer $priority
     * @throws \InvalidArgumentException
     * @internal param $ callable|filter class name|\Sirius\Filtration\Filter\AbstractFilter $callbackOrFilterName
     * @internal param array|string $params
     * @return self
     */
    public function add($selector, $callbackOrFilterName = null, $options = null, $recursive = false, $priority = 0)
    {
        /**
         * $selector is actually an array with filters
         *
         * @example $filtrator->add([
         *              'title' => ['trim', ['truncate', '{"limit":100}']]
         *              'description' => ['trim']
         *          ]);
         */
        if (is_array($selector)) {
            foreach ($selector as $key => $filters) {
                $this->add($key, $filters);
            }
            return $this;
        }

        if (! is_string($selector)) {
            throw new \InvalidArgumentException('The data selector for filtering must be a string');
        }


        if (is_string($callbackOrFilterName)) {
            // rule was supplied like 'trim' or 'trim | nullify'
            if (strpos($callbackOrFilterName, ' | ') !== false) {
                return $this->add($selector, explode(' | ', $callbackOrFilterName));
            }
            // rule was supplied like this 'trim(limit=10)(true)(10)'
            if (strpos($callbackOrFilterName, '(') !== false) {
                list($callbackOrFilterName, $options, $recursive, $priority) = $this->parseRule($callbackOrFilterName);
            }
        }

        /**
         * The $callbackOrFilterName is an array of filters
         *
         * @example $filtrator->add('title', [
         *          'trim',
         *          ['truncate', '{"limit":100}']
         *      ]);
         */
        if (is_array($callbackOrFilterName) && ! is_callable($callbackOrFilterName)) {
            foreach ($callbackOrFilterName as $filter) {
                // $filter is something like ['truncate', '{"limit":100}']
                if (is_array($filter) && ! is_callable($filter)) {
                    $this->add($selector, ...$filter);
                } elseif (is_string($filter) || is_callable($filter)) {
                    $this->add($selector, $filter);
                }
            }
            return $this;
        }

        $filter = $this->filterFactory->createFilter($callbackOrFilterName, $options, $recursive);
        if (! array_key_exists($selector, $this->filters)) {
            $this->filters[$selector] = new FilterSet();
        }
        /* @var $filterSet FilterSet */
        $filterSet = $this->filters[$selector];
        $filterSet->insert($filter, $priority);
        $this->compiledAllowedSelectors = [];

        return $this;
    }

    /**
     * Converts a rule that was supplied as string into a set of options that define the rule
     *
     * @example 'minLength({"min":2})(true)(10)'
     *
     *          will be converted into
     *
     *          [
     *          'minLength', // validator name
     *          ['min' => 2'], // validator options
     *          true, // recursive
     *          10 // priority
     *          ]
     * @param string $ruleAsString
     * @return array
     */
    protected function parseRule($ruleAsString)
    {
        $ruleAsString = trim($ruleAsString);

        $options = [];
        $recursive = false;
        $priority = 0;

        $name = substr($ruleAsString, 0, strpos($ruleAsString, '('));
        $ruleAsString = substr($ruleAsString, strpos($ruleAsString, '('));
        $matches = [];
        preg_match_all('/\(([^\)]*)\)/', $ruleAsString, $matches);

        if (isset($matches[1])) {
            if (isset($matches[1][0]) && $matches[1][0]) {
                $options = $matches[1][0];
            }
            if (isset($matches[1][1]) && $matches[1][1]) {
                $recursive = (in_array($matches[1][1], array(true, 'TRUE', 'true', 1))) ? true : false;
            }
            if (isset($matches[1][2]) && $matches[1][2]) {
                $priority = (int)$matches[1][2];
            }
        }

        return [
            $name,
            $options,
            $recursive,
            $priority
        ];
    }

    /**
     * Remove a filter from the stack
     *
     * @param string $selector
     * @param bool|callable|string|TRUE $callbackOrName
     * @throws \InvalidArgumentException
     * @return \Sirius\Filtration\Filtrator
     */
    public function remove($selector, $callbackOrName = true)
    {
        if (array_key_exists($selector, $this->filters)) {
            if ($callbackOrName === true) {
                unset($this->filters[$selector]);
            } else {
                if (! is_object($callbackOrName)) {
                    $filter = $this->filterFactory->createFilter($callbackOrName);
                } else {
                    $filter = $callbackOrName;
                }
                /* @var $filterSet FilterSet */
                $filterSet = $this->filters[$selector];
                $filterSet->remove($filter);
            }
        }
        $this->compiledAllowedSelectors = [];

        return $this;
    }

    /**
     * Retrieve all filters stack
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Apply filters to an array
     *
     * @param array $data
     * @return array
     */
    public function filter(array $data = [])
    {
        // first apply the filters to the ROOT
        if (isset($this->filters[self::SELECTOR_ROOT])) {
            /* @var $rootFilters FilterSet */
            $rootFilters = $this->filters[self::SELECTOR_ROOT];
            $data = $rootFilters->applyFilters($data);
        }

        $this->compileAllowedSelectors();

        $result = [];
        foreach ($data as $key => $value) {
            if ($this->itemIsAllowed($key)) {
                $result[$key] = $this->filterItem($data, $key);
            }
        }
        return $result;
    }

    /**
     * Apply filters on a single item in the array
     *
     * @param array $data
     * @param string $valueIdentifier
     * @return mixed
     */
    public function filterItem($data, $valueIdentifier)
    {
        $value = Utils::arrayGetByPath($data, $valueIdentifier);
        $value = $this->applyFilters($value, $valueIdentifier, $data);
        if (is_array($value)) {
            $result = [];
            foreach (array_keys($value) as $k) {
                if ($this->itemIsAllowed("{$valueIdentifier}[{$k}]")) {
                    $result[$k] = $this->filterItem($data, "{$valueIdentifier}[{$k}]");
                }
            }
            return $result;
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
     * @param mixed $context
     * @return mixed
     */
    public function applyFilters($value, $valueIdentifier, $context)
    {
        foreach ($this->filters as $selector => $filterSet) {
            /* @var $filterSet FilterSet */
            if ($selector != self::SELECTOR_ROOT && Utils::itemMatchesSelector($valueIdentifier, $selector)) {
                $value = $filterSet->applyFilters($value, $valueIdentifier, $context);
            }
        }
        return $value;
    }

    private function itemIsAllowed($item)
    {
        if (empty($this->compiledAllowedSelectors)) {
            return true;
        }
        foreach ($this->compiledAllowedSelectors as $selector) {
            if (Utils::itemMatchesSelector($item, $selector)) {
                return true;
            }
        }
        return false;
    }

    private function compileAllowedSelectors()
    {
        if (!empty($this->compiledAllowedSelectors)) {
            return;
        }

        $selectors = array_unique(array_merge(
            array_values($this->allowedSelectors),
            array_keys($this->filters)
        ));

        $compiled = [];

        foreach ($selectors as $selector) {
            if ($selector == '/' || $selector == '*') {
                continue;
            }
            $compiled[] = $selector;
            while ($lastPart = strrpos($selector, '[')) {
                $parent = substr($selector, 0, $lastPart);
                if (!in_array($parent, $compiled)) {
                    $compiled[] = $parent;
                }
                $selector = $parent;
            }
        }

        $this->compiledAllowedSelectors = $compiled;
    }
}
