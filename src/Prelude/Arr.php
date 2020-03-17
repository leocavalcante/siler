<?php declare(strict_types=1);

namespace Siler\Arr;

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param array $array
 * @param string $key
 * @param mixed $value
 * @return array
 */
function set(array &$array, string $key, $value)
{
    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    /** @psalm-suppress MixedAssignment */
    $array[array_shift($keys)] = $value;
    return $array;
}
