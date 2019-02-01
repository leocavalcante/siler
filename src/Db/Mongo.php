<?php

declare(strict_types=1);

namespace Siler\Mongo;

use Siler\Container;

const MONGODB_DEFAULT_NAME = 'mongodb';

function connect($uri = 'mongodb://127.0.0.1/', array $uriOptions = [], array $driverOptions = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Client
{
    $client = new \MongoDB\Client($uri, $uriOptions, $driverOptions);
    Container\set($clientName, $client);
    return $client;
}

function database(string $databaseName, array $options = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Database
{
    if (!Container\has($clientName)) {
        return null;
    }

    return Container\get($clientName)->selectDatabase($databaseName, $options);
}

function collection(string $databaseName, $collectionName, array $options = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Collection
{
    if (!Container\has($clientName)) {
        return null;
    }

    return Container\get($clientName)->selectCollection($databaseName, $collectionName, $options);
}
