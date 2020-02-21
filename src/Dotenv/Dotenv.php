<?php declare(strict_types=1);
/*
 * Helpers functions to work with vlucas/phpdotenv.
 */

namespace Siler\Dotenv;

use Dotenv\Dotenv;
use UnexpectedValueException;
use function Siler\array_get;

/**
 * @param string $path Directory name of the .env file location *
 * @return array<array-key, null|string>
 * @return array
 * @deprecated Will be removed on the next release.
 *
 */
function init(string $path): array
{
    $dot_env = Dotenv::create($path);
    return $dot_env->load();
}

/**
 * @param string|null $key
 * @param mixed $default A default when the key do not exists
 *
 * @return string|null|array<string, string>
 * @deprecated Use function Siler\Env\env_var.
 *
 */
function env(?string $key = null, ?string $default = null)
{
    /** @var array<string, string> $_SERVER */
    return array_get($_SERVER, $key, $default);
}

/**
 * @param string $key
 * @param int|null $default
 *
 * @return int|null
 * @deprecated Use function Siler\Env\env_int.
 *
 */
function int_val(string $key, ?int $default = null): ?int
{
    /** @var array<string, string> $_SERVER */
    $val = array_get($_SERVER, $key, $default);

    if ($val === null) {
        return $default;
    }

    if (is_numeric($val)) {
        return intval($val);
    }

    return $default;
}

/**
 * @param string $key
 * @param bool|null $default
 *
 * @return bool|null
 * @deprecated Use function Siler\Env\env_bool.
 *
 */
function bool_val(string $key, ?bool $default = null): ?bool
{
    /** @var array<string, string> $_SERVER */
    $val = array_get($_SERVER, $key, $default);

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
 * @param string $key
 *
 * @return true
 * @deprecated Environment vars getters already checks if there no default.
 *
 */
function requires(string $key): bool
{
    if (array_key_exists($key, $_ENV)) {
        return true;
    }

    throw new UnexpectedValueException("$key is not set in the environment variables");
}
