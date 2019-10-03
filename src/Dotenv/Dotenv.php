<?php

declare(strict_types=1);

/*
 * Helpers functions to work with vlucas/phpdotenv.
 */

namespace Siler\Dotenv;

use Dotenv\Dotenv;
use UnexpectedValueException;

use function Siler\array_get;

/**
 * Load the .env file contents into the environment.
 *
 * @param string $path Directory name of the .env file location
 *
 * @return array
 */
function init(string $path): array
{
    $dotenv = Dotenv::create($path);

    return $dotenv->load();
}

/**
 * Get an environment value or fallback to the given default.
 *
 * @param string|null $key
 * @param mixed $default A default when the key do not exists
 *
 * @return mixed
 */
function env(?string $key = null, $default = null)
{
    return array_get($_SERVER, $key, $default);
}

/**
 * Returns an environment variable as an integer.
 *
 * @param string $key
 * @param int|null $default
 *
 * @return int|null
 */
function int_val(string $key, int $default = null): ?int
{
    $val = env($key, $default);

    if (is_numeric($val)) {
        return intval($val);
    }

    return $default;
}

/**
 * Returns an environment variable as an boolean.
 *
 * @param string $key
 * @param bool|null $default
 *
 * @return bool|null
 */
function bool_val(string $key, bool $default = null): ?bool
{
    $val = env($key, $default);

    if ($val === null) {
        return $default;
    }

    if ($val === 'false') {
        return false;
    }

    if ($val === '[]') {
        return false;
    }

    if ($val === '{}') {
        return false;
    }

    return boolval($val);
}

/**
 * Checks for the presence of an environment variable.
 *
 * @param string $key
 *
 * @return bool
 */
function requires(string $key): bool
{
    if (array_key_exists($key, $_ENV)) {
        return true;
    }

    throw new UnexpectedValueException("$key is not set in the environment variables");
}
