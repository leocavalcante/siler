<?php

declare(strict_types=1);
/**
 * Module to work with MongoDB operations.
 */

namespace Siler\Mongo;

use Siler\Container;

const MONGODB_DEFAULT_NAME = 'mongodb';

/**
 * Creates a new MongoDB\Client instance.
 *
 * @param string $uri
 * @param array $uriOptions
 * @param array $driverOptions
 * @param string $clientName
 *
 * @return \MongoDB\Client
 */
function connect(string $uri = 'mongodb://127.0.0.1/', array $uriOptions = [], array $driverOptions = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Client
{
    $client = new \MongoDB\Client($uri, $uriOptions, $driverOptions);
    Container\set($clientName, $client);

    return $client;
}

/**
 * Selects a database from a MongoDB client.
 *
 * @param string $databaseName
 * @param array $options
 * @param string $clientName
 *
 * @return \MongoDB\Database
 */
function database(string $databaseName, array $options = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Database
{
    if (!Container\has($clientName)) {
        throw new \OutOfRangeException("$clientName not found");
    }

    return Container\get($clientName)->selectDatabase($databaseName, $options);
}

/**
 * Selects a collection from a database and client.
 *
 * @param string $databaseName
 * @param string $collectionName
 * @param array $options
 * @param string $clientName
 *
 * @return \MongoDB\Collection
 */
function collection(string $databaseName, string $collectionName, array $options = [], string $clientName = MONGODB_DEFAULT_NAME): \MongoDB\Collection
{
    if (!Container\has($clientName)) {
        throw new \OutOfRangeException("$clientName not found");
    }

    return Container\get($clientName)->selectCollection($databaseName, $collectionName, $options);
}
