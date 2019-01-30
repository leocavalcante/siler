<?php

declare(strict_types=1);

namespace Siler\Str;

use Cocur\Slugify\Slugify;
use Siler\Container;

function slugify(string $input, ?array $opts = null): string
{
    if (!Container\has('slugify')) {
        Container\set('slugify', new Slugify());
    }

    return Container\get('slugify')->slugify($input, $opts);
}
