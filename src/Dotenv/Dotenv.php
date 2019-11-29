<?php declare(strict_types=1);
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
 * @param string $path Directory name of the .env file location *
 * @psalm-return array<array-key, null|string>
 * @return array
 */
function init(string $path): array
{
    $dot_env = Dotenv::create($path);
    return $dot_env->load();
}

/**
 * Get an environment value or fallback to the given default.
 *
 * @param string|null $key
 * @param mixed $default A default when the key do not exists
 *
 * @return string|null|array<string, string>
 */
function env(?string $key = null, ?string $default = null)
{
    /** @var array<string, string> $_SERVER */
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
function int_val(string $key, ?int $default = null): ?int
{
    $val = env($key);

    if ($val === null) {
        return $default;
    }

    if (!is_numeric($val)) {
        return $default;
    }

    return intval($default);
}

/**
 * Returns an environment variable as an boolean.
 *
 * @param string $key
 * @param bool|null $default
 *
 * @return bool|null
 */
function bool_val(string $key, ?bool $default = null): ?bool
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
 * @return true
 */
function requires(string $key): bool
{
    if (array_key_exists($key, $_ENV)) {
        return true;
    }

    throw new UnexpectedValueException("$key is not set in the environment variables");
}
