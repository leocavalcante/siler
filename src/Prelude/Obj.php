<?php declare(strict_types=1);

namespace Siler\Obj;

use function Siler\Str\camel_case;

/**
 * Patches array values on an object props.
 *
 * @template T as object
 * @param mixed $target
 * @psalm-param T $target
 * @param array<string, mixed> $source
 * @return mixed
 * @psalm-return T
 */
function patch($target, array $source)
{
    /** @psalm-suppress MixedAssignment */
    foreach ($source as $key => $value) {
        $prop = lcfirst(camel_case($key));

        if (property_exists($target, $prop)) {
            $target->{$prop} = $value;
        }
    }

    return $target;
}
