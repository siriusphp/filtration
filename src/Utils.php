<?php
declare(strict_types=1);
namespace Sirius\Filtration;

class Utils
{
    protected static function getSelectorParts($selector)
    {
        $firstOpen = strpos((string) $selector, '[');
        if ($firstOpen === false) {
            return [$selector, ''];
        }
        $firstClose = strpos($selector, ']');
        $container = substr($selector, 0, $firstOpen);
        $subselector = substr($selector, $firstOpen + 1, $firstClose - $firstOpen - 1)
                       . substr($selector, $firstClose + 1);
        return [$container, $subselector];
    }

    /**
     * Retrieves an element from an array via its path
     * Path examples:
     *   key
     *   key[subkey]
     *   key[0][subkey]
     *
     * @param  array $array
     * @param  string $path
     * @return mixed
     */
    public static function arrayGetByPath($array, $path)
    {
        list($container, $subpath) = self::getSelectorParts($path);
        if ($subpath === '') {
            return array_key_exists($container, $array) ? $array[$container] : null;
        }
        return array_key_exists($container, $array) ? self::arrayGetByPath($array[$container], $subpath) : null;
    }

    public static function itemMatchesSelector($item, $selector)
    {
        // the selector is a simple path identifier
        // NOT something like key[*][subkey]
        if (strpos($selector, '*') === false) {
            return $item === $selector;
        }
        $regex = '/' . str_replace('*', '[^\]]+', str_replace([
                '[',
                ']'
            ], [
                '\[',
                '\]'
            ], $selector)) . '/';
        return preg_match($regex, (string) $item);
    }
}
