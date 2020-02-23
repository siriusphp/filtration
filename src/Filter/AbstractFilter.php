<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

abstract class AbstractFilter
{
    protected $recursive = true;

    protected $options = array();

    protected $context;

    public function __construct($options = array(), $recursive = true)
    {
        $options = $this->normalizeOptions($options);
        if (is_array($options) && ! empty($options)) {
            foreach ($options as $k => $v) {
                $this->setOption($k, $v);
            }
        }
        $this->recursive = $recursive;
    }

    protected function normalizeOptions($options)
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
            throw new \InvalidArgumentException('Filtrator options should be an array, JSON string or query string');
        }

        return $options;
    }

    /**
     * Generates a unique string to identify the validator.
     * It can be used to compare 2 validators
     * (eg: so you don't add the same validator twice in a validator object)
     *
     * @return string
     */
    public function getUniqueId()
    {
        return get_called_class() . '|' . json_encode(ksort($this->options));
    }

    /**
     * Set an option for the validator.
     *
     * The options are also be passed to the error message.
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * The context of the validator can be used when the validator depends on other values
     * that are not known at the moment the validator is constructed
     * For example, when you need to validate an email field matches another email field,
     * to confirm the email address
     *
     * @param array|object $context
     * @return self
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function filter($value, $valueIdentifier = null)
    {
        if ($this->recursive && is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $vIdentifier = ($valueIdentifier) ? "{$valueIdentifier}[{$k}]" : $k;
                $result[$k] = $this->filter($v, $vIdentifier);
            }
            return $result;
        } else {
            return $this->filterSingle($value, $valueIdentifier);
        }
    }

    abstract public function filterSingle($value, $valueIdentifier = null);
}
