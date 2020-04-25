<?php declare(strict_types=1);

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
    return strpos($haystack, $needle) === 0;
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
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
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
    return strpos($haystack, $needle) !== false;
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

/**
 * Converts a snake_case string to CamelCase.
 *
 * @param string $input
 * @return string
 */
function camel_case(string $input): string
{
    return str_replace('_', '', ucwords($input, '_'));
}

/**
 * Multi-byte alternative for ucfirst().
 *
 * @param string $input
 * @return string
 */
function mb_ucfirst(string $input): string
{
    return mb_strtoupper(mb_substr($input, 0, 1)) . mb_substr($input, 1);
}

/**
 * Multi-byte alternative for lcfirst().
 *
 * @param string $input
 * @return string
 */
function mb_lcfirst(string $input): string
{
    return mb_strtolower(mb_substr($input, 0, 1)) . mb_substr($input, 1);
}
