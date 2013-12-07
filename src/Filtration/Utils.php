<?php
/**
 * class copied from Sirius/Validation/Utils which is fully tested
 *
 */
namespace Sirius\Filtration;


class Utils  {

    protected static function getSelectorParts($selector) {
        $firstOpen = strpos($selector, '[');
        if ($firstOpen === false) {
            return array($selector, '');
        }
        $firstClose = strpos($selector, ']');
        $container = substr($selector, 0, $firstOpen);
        $subselector = substr($selector, $firstOpen + 1, $firstClose - $firstOpen - 1) . substr($selector, $firstClose + 1);
        return array($container, $subselector);
    }

    /**
     * Retrieves an element from an array via its path
     * Path examples:
     * 		key
     * 		key[subkey]
     * 		key[0][subkey]
     * 
     * @param  array $array
     * @param  string $path
     * @return mixed
     */
    static function arrayGetByPath($array, $path) {
        list($container, $subpath) = self::getSelectorParts($path);
        if ($subpath === '') {
            return array_key_exists($container, $array) ? $array[$container] : null;
        }
        return array_key_exists($container, $array) ? self::arrayGetByPath($array[$container], $subpath) : null;
    }

}