<?php

declare(strict_types=1);

namespace Siler\Container;

use function Siler\array_get;

/**
 * Get a value from the container.
 *
 * @param string $key     The key to be searched on the container
 * @param mixed  $default Default value when the key does not exists on the container
 *
 * @return mixed
 */
function get(string $key, $default = null)
{
    $container = Container::getInstance();

    return array_get($container->values, $key, $default);
}


/**
 * Set a value in the container.
 *
 * @param string $key   Identified by the given key
 * @param mixed  $value The value to be stored
 *
 * @return void
 */
function set(string $key, $value)
{
    $container = Container::getInstance();
    $container->values[$key] = $value;
}


/**
 * Checks if there is some value in the given $key.
 *
 * @param string $key Key to search in the Container.
 *
 * @return bool
 */
function has(string $key) : bool
{
    $container = Container::getInstance();

    return array_key_exists($key, $container->values);
}


/**
 *
 * @internal DIC.
 * @ignore   Not part of the API
 */
final class Container
{
    /**
     * Singleton -> instance.
     */
    public static function getInstance() : self
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }


    /**
     *  The actual holder.
     *
     * @var array
     */
    public $values = [];


    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}//end class
