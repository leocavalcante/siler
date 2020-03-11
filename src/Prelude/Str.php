<?php

declare(strict_types=1);

/*
 * String module. Functions to operate on strings.
 */

namespace Siler\Str;

use Cocur\Slugify\Slugify;
use Siler\Container;

/**
 * Slugify a string.
 *
 * @param string $input
 * @param array|null $opts
 *
 * @return string
 */
function slugify(string $input, ?array $opts = null): string
{
    if (!Container\has('slugify')) {
        Container\set('slugify', new Slugify());
    }

    /** @var Slugify $slugify */
    $slugify = Container\get('slugify');
    return $slugify->slugify($input, $opts);
}

/**
 * Breaks a string into lines.
 *
 * @param string $input
 *
 * @return array<int, string>
 */
function lines(string $input): array
{
    return array_map(function (string $row): string {
        return trim($row);
    }, preg_split('/\n/', $input));
}

/**
 * Checks if a string starts with another string.
 *
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function starts_with(string $haystack, string $needle): bool
{
    return mb_strpos($haystack, $needle) === 0;
}

/**
 * Checks if a string ends by another string.
 *
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function ends_with(string $haystack, string $needle): bool
{
    return starts_with(strrev($haystack), strrev($needle));
}

/**
 * Checks if a string contains another string.
 *
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function contains(string $haystack, string $needle): bool
{
    return mb_strpos($haystack, $needle) > -1;
}

/**
 * Converts a CamelCase string to snake_case.
 *
 * @param string $input
 * @return string
 */
function snake_case(string $input): string
{
    return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $input));
}
