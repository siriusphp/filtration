<?php
declare(strict_types=1);
namespace Sirius\Filtration\Filter;

class Callback extends AbstractFilter
{
    const OPTION_CALLBACK = 'callback';

    const OPTION_ARGUMENTS = 'arguments';

    public function getUniqueId(): string
    {
        $uniqueId = get_called_class() . '|';
        // the callback is a function name (eg: strip_tags) or a static class method (eg: MyClass::method)
        if (is_string($this->options['callback'])) {
            $uniqueId .= $this->options['callback'];
        } elseif (is_array($this->options['callback'])) {
            // the callback is an array that points to a static class method (eg: ['MyClass', 'method'])
            if (is_string($this->options['callback'][0])) {
                $uniqueId .= implode('::', $this->options['callback']);
            } elseif (is_object($this->options['callback'][0])) {
                $uniqueId .= spl_object_hash($this->options['callback'][0]) . '->' . $this->options['callback'][1];
            }
        } elseif (is_object($this->options['callback']) && $this->options['callback'] instanceof \Closure) {
            $uniqueId .= spl_object_hash($this->options['callback']);
        }
        if (isset($this->options['arguments'])) {
            $args = $this->options['arguments'];
            ksort($args);
            $uniqueId .= json_encode($args);
        }
        return $uniqueId;
    }

    public function filterSingle($value, string $valueIdentifier = null)
    {
        if (isset($this->options['callback']) && is_callable($this->options['callback'])) {
            $args = (isset($this->options['arguments'])) ? (array) $this->options['arguments'] : [];
            array_unshift($args, $value);
            array_push($args, $valueIdentifier, $this->context);
            $value = call_user_func_array($this->options['callback'], $args);
        }
        return $value;
    }
}
