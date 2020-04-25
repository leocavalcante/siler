<?php declare(strict_types=1);
/*
 * Siler core file.
 */

namespace Siler;

use Closure;
use UnexpectedValueException;

const ARRAY_GET_ERROR_MESSAGE = "Key (%s) not found in array and no default was provided.";

/**
 * Get a value from an array checking if the key exists and returning a default value if not.
 *
 * @template T
 * @param array<array-key, mixed>|null $array
 * @psalm-param array<array-key, T>|null $array
 * @param array-key|null $key The key to be searched
 * @param mixed|null $default The default value to be returned when the key don't exists
 * @psalm-param T|null $default The default value to be returned when the key don't exists
 * @param bool $caseInsensitive Ignore key case, default false
 * @return mixed|null|array<array-key, mixed>
 * @psalm-return T|null|array<string, T>
 * @psalm-suppress LessSpecificReturnType
 */
function array_get(?array $array, $key = null, $default = null, bool $caseInsensitive = false)
{
    if ($array === null) {
        return $default;
    }

    if ($key === null) {
        return $array;
    }

    if ($caseInsensitive && is_string($key)) {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $array = array_change_key_case($array);
        $key = strtolower($key);
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Type-safe array_get for strings.
 *
 * @template T
 * @param array<array-key, mixed> $array
 * @psalm-param array<string, T> $array
 * @param string $key
 * @param string|null $default
 * @return string
 */
function array_get_str(array $array, string $key, ?string $default = null): string
{
    $value = array_get($array, $key);

    if ($value === null && $default === null) {
        throw new UnexpectedValueException(sprintf(ARRAY_GET_ERROR_MESSAGE, $key));
    }

    if ($value === null) {
        return $default;
    }

    return strval($value);
}

/**
 * Type-safe array_get for integers.
 *
 * @template T
 * @param array<array-key, mixed> $array
 * @psalm-param array<string, T> $array
 * @param string $key
 * @param int|null $default
 * @return int
 */
function array_get_int(array $array, string $key, ?int $default = null): int
{
    /** @var mixed $value */
    $value = array_get($array, $key);

    if ($value === null && $default === null) {
        throw new UnexpectedValueException(sprintf(ARRAY_GET_ERROR_MESSAGE, $key));
    }

    if ($value === null) {
        return $default;
    }

    return intval($value);
}

/**
 * Type-safe array_get for floats.
 *
 * @template T
 * @param array<array-key, mixed> $array
 * @psalm-param array<string, T> $array
 * @param string $key
 * @param float|null $default
 * @return float
 */
function array_get_float(array $array, string $key, ?float $default = null): float
{
    /** @var mixed $value */
    $value = array_get($array, $key);

    if ($value === null && $default === null) {
        throw new UnexpectedValueException(sprintf(ARRAY_GET_ERROR_MESSAGE, $key));
    }

    if ($value === null) {
        return $default;
    }

    return floatval($value);
}

/**
 * Type-safe array_get for booleans.
 *
 * @template T
 * @param array<array-key, mixed> $array
 * @psalm-param array<string, T> $array
 * @param string $key
 * @param bool|null $default
 * @return bool
 */
function array_get_bool(array $array, string $key, ?bool $default = null): bool
{
    /** @var mixed $value */
    $value = array_get($array, $key);

    if ($value === null && $default === null) {
        throw new UnexpectedValueException(sprintf(ARRAY_GET_ERROR_MESSAGE, $key));
    }

    if ($value === null) {
        return $default;
    }

    return boolval($value);
}

/**
 * Type-safe array_get for arrays.
 *
 * @template T
 * @param array<array-key, mixed> $array
 * @psalm-param array<string, T> $array
 * @param string $key
 * @param array|null $default
 * @return array
 */
function array_get_arr(array $array, string $key, ?array $default = null): array
{
    $value = array_get($array, $key);

    if ($value === null && $default === null) {
        throw new UnexpectedValueException(sprintf(ARRAY_GET_ERROR_MESSAGE, $key));
    }

    if ($value === null) {
        return $default;
    }

    return (array) $value;
}

/**
 * Returns a function that requires the given filename.
 *
 * @param string $filename The file to be required
 * @return Closure(string[]):(false|mixed|null)
 */
function require_fn(string $filename): Closure
{
    return
        /**
         * @param array $params
         * @return mixed
         */
        static function (array $params = []) use ($filename) {
            if (!file_exists($filename)) {
                return null;
            }

            if (!Container\has($filename)) {
                /** @noinspection PhpIncludeInspection */
                Container\set($filename, include_once $filename);
            }

            /** @var mixed $value */
            $value = Container\get($filename);

            if (is_callable($value)) {
                return call_user_func($value, $params);
            }

            return $value;
        };
}
