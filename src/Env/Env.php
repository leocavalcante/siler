<?php declare(strict_types=1);

namespace Siler\Env;

use UnexpectedValueException;

/**
 * Gets a variable from the environment.
 *
 * @param string $key
 * @param string|null $default
 *
 * @return string
 */
function env_var(string $key, ?string $default = null): string
{
    $value = getenv($key);

    if ($value === false) {
        if ($default === null) {
            throw new UnexpectedValueException("Environment variable $key not found");
        }

        return $default;
    }

    return $value;
}

/**
 * Gets a variable from environment as an integer.
 *
 * @param string $key
 * @param int|null $default
 *
 * @return int
 */
function env_int(string $key, ?int $default = null): int
{
    return intval(env_var($key, $default === null ? $default : strval($default)));
}

/**
 * Gets a variable from environment as a boolean.
 *
 * @param string $key
 * @param bool|null $default
 *
 * @return bool
 */
function env_bool(string $key, ?bool $default = null): bool
{
    $value = env_var($key, $default === null ? $default : strval($default));

    if (in_array($value, ['false', '0', '{}', '[]', 'null', 'undefined'])) {
        return false;
    }

    return boolval($value);
}
