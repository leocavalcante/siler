<?php declare(strict_types=1);

namespace Siler\Config;

use Noodlehaus\Config;
use Noodlehaus\Parser\ParserInterface;
use Siler\Container;

const CONFIG = 'siler_config';

/**
 * Gets or sets a configuration.
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed|null
 */
function config(string $key, $default = null)
{
    /** @var Config|null $config */
    $config = Container\get(CONFIG);

    if ($config === null) {
        return null;
    }

    return $config->get($key, $default);
}

/**
 * Load configuration values.
 *
 * @param string|array $values Filenames or string with configuration
 * @return Config
 */
function load($values, ParserInterface $parser = null, $string = false): Config
{
    $config = new Config($values, $parser, $string);
    Container\set(CONFIG, $config);
    return $config;
}

/**
 * Checks if the key exists on the config.
 *
 * @param string $key
 * @return bool
 */
function has(string $key): bool
{
    /** @var Config|null $config */
    $config = Container\get(CONFIG);

    if ($config === null) {
        return false;
    }

    return $config->has($key);
}

/**
 * Returns all the configurations.
 *
 * @return array|null
 */
function all(): ?array
{
    /** @var Config|null $config */
    $config = Container\get(CONFIG);

    if ($config === null) {
        return null;
    }

    return $config->all();
}
