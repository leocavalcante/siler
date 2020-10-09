<?php declare(strict_types=1);

namespace Siler\Container;

use OverflowException;
use UnderflowException;
use function Siler\Functional\call;
use function Siler\array_get;

/**
 * Get a value from the container.
 *
 * @param string $key The key to be searched on the container
 * @param mixed $default Default value when the key does not exists on the container
 * @var mixed $value
 * @return mixed|null
 */
function get(string $key, $default = null)
{
    $container = Container::getInstance();
    $value = array_get($container->values, $key, $default);

    if (is_callable($value)) {
        return call($value);
    }

    return $value;
}

/**
 * Set a value in the container.
 *
 * @param string $key Identified by the given key
 * @param mixed $value The value to be stored
 */
function set(string $key, $value): void
{
    $container = Container::getInstance();
    $container->values[$key] = $value;
}

/**
 * Checks if there is some value in the given $key.
 *
 * @param string $key Key to search in the Container.
 * @return bool
 */
function has(string $key): bool
{
    $container = Container::getInstance();

    return array_key_exists($key, $container->values);
}

/**
 * Clears the value on the container.
 *
 * @param string $key
 */
function clear(string $key): void
{
    $container = Container::getInstance();
    unset($container->values[$key]);
}

/**
 * Sugar for Container\set that throws an OverflowException when the key is already in use.
 * Useful for dependency injection.
 *
 * @param string $serviceName
 * @param mixed $service
 */
function inject(string $serviceName, $service): void
{
    $container = Container::getInstance();

    if (array_key_exists($serviceName, $container->values)) {
        throw new OverflowException("$serviceName already in use");
    }

    $container->values[$serviceName] = $service;
}

/**
 * Sugar for Container\get that throws an UnderflowException when the key isn't initialized.
 * Useful for dependency injection/IoC.
 *
 * @param string $serviceName
 * @var mixed $service
 * @return mixed
 */
function retrieve(string $serviceName)
{
    $container = Container::getInstance();

    if (!array_key_exists($serviceName, $container->values)) {
        throw new UnderflowException("$serviceName not initialized");
    }

    $service = $container->values[$serviceName];

    if (is_callable($service)) {
        return call($service);
    }

    return $service;
}

/**
 * @internal Class Container
 * @package Siler\Container
 */
final class Container
{
    /** @var array<string, mixed> */
    public $values = [];

    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        /** @var Container|null */
        static $instance = null;

        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }
}
