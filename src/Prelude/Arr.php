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

/**
 * Creates an array of associative arrays using the first element as keys.
 *
 * @param array $arr
 * @return array
 */
function assoc(array $arr): array
{
    /** @var array<array-key, array-key> $head */
    $head = $arr[0];

    return array_map(static function (array $row) use ($head): array {
        return array_combine($head, $row);
    }, array_slice($arr, 1));
}
