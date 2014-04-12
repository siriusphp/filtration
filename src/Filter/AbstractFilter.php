<?php
namespace Sirius\Filtration\Filter;

abstract class AbstractFilter
{

    protected $recursive = true;

    protected $options = array();

    protected $context;

    function __construct($options = array(), $recursive = true)
    {
        if (is_array($options) && ! empty($options)) {
            foreach ($options as $k => $v) {
                $this->setOption($k, $v);
            }
        }
        $this->recursive = $recursive;
    }

    /**
     * Generates a unique string to identify the validator.
     * It can be used to compare 2 validators
     * (eg: so you don't add the same validator twice in a validator object)
     *
     * @return string
     */
    function getUniqueId()
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
    function setOption($name, $value)
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
    function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    function filter($value, $valueIdentifier = null)
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

    abstract function filterSingle($value, $valueIdentifier = null);
}
