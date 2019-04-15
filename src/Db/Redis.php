<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Siler\Redis;

use Redis;
use Siler\Container;

/**
 * Default Redis instance name on Siler\Container.
 */
const DEFAULT_INSTANCE = 'redis_default_instance';

/**
 * Creates an instance and connects to a Redis server.
 *
 * @param string $host
 * @param int $port
 * @param string $redisInstance
 *
 * @return Redis
 */
function connect(string $host = '127.0.0.1', int $port = 6379, string $redisInstance = DEFAULT_INSTANCE): Redis
{
    $redis = new Redis();
    $redis->connect($host, $port);

    Container\set($redisInstance, $redis);

    return $redis;
}

/**
 * Gets the value from the given $key.
 *
 * @param string $key
 * @param string $redisInstance
 *
 * @return mixed
 */
function get(string $key, string $redisInstance = DEFAULT_INSTANCE)
{
    $redis = Container\get($redisInstance);
    return $redis->get($key);
}

/**
 * Sets a value on the given $key.
 *
 * @param string $key
 * @param string $val
 * @param string $redisInstance
 *
 * @return mixed
 */
function set(string $key, string $val, string $redisInstance = DEFAULT_INSTANCE)
{
    $redis = Container\get($redisInstance);
    return $redis->set($key, $val);
}

/**
 * Checks if the key exists.
 *
 * @param string $key
 *
 * @param string $redisInstance
 * @return bool
 */
function has(string $key, string $redisInstance = DEFAULT_INSTANCE): bool
{
    $redis = Container\get($redisInstance);
    return $redis->exists($key) > 0;
}
