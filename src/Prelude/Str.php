<?php

declare(strict_types=1);
/**
 * String module. Functions to operate on strings.
 */

namespace Siler\Str;

use Cocur\Slugify\Slugify;
use Siler\Container;

/**
 * Slugify a string.
 *
 * @param string     $input
 * @param array|null $opts
 *
 * @return string
 */
function slugify(string $input, ?array $opts = null): string
{
    if (!Container\has('slugify')) {
        Container\set('slugify', new Slugify());
    }

    return Container\get('slugify')->slugify($input, $opts);
}
