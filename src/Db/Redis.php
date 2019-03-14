<?php

namespace  Siler\Redis;

use Siler\Container;

const DEFAULT_INSTANCE = 'redis_default_instance';

function connect(string $host = '127.0.0.1', int $port = 6379, string $redisInstance = DEFAULT_INSTANCE): \Redis
{
    $redis = new \Redis();
    $redis->connect($host, $port);

    Container\set($redisInstance, $redis);

    return $redis;
}


function get(string $key, string $redisInstance = DEFAULT_INSTANCE)
{
    $redis = Container\get(DEFAULT_INSTANCE);
    return $redis->get($key);
}

function set(string $key, string $val, string $redisInstance = DEFAULT_INSTANCE)
{
    $redis = Container\get(DEFAULT_INSTANCE);
    return $redis->set($key, $val);
}

function has(string $key): bool
{
    $redis = Container\get(DEFAULT_INSTANCE);
    return $redis->exists($key) > 0;
}
