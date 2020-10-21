<?php declare(strict_types=1);

namespace Siler\Prelude;

/**
 * Creates a new Siler collection from the given iterable.
 *
 * @param iterable $items
 * @return Collection
 */
function collect(iterable $items): Collection
{
    return Collection::of($items);
}
