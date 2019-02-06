<?php

declare(strict_types=1);
/**
 * Module to work with MongoDB operations.
 */

namespace Siler\Mongo;

use Siler\Container;

const MONGODB_DEFAULT_NAME = 'mongodb';
const MONGODB_USING_DBNAME = 'mongodb_dbname';

/**
 * Creates a new MongoDB\Client instance.
 *
 * @param string $uri
 * @param array  $uriOptions
 * @param array  $driverOptions
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
 * @param array  $options
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
 * @param array  $options
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

/**
 * Sugar to create a new ObjectId.
 *
 * @param string $oid
 *
 * @return \MongoDB\BSON\ObjectId
 */
function oid(string $oid): \MongoDB\BSON\ObjectId
{
    return new \MongoDB\BSON\ObjectId($oid);
}

function using(string $databaseName)
{
    Container\set(MONGODB_USING_DBNAME, $databaseName);
}

function find(string $collectionName, array $filter = [], array $options = []): \MongoDB\Driver\Cursor
{
    return collection(__get_dbname_or_throw(), $collectionName)->find($filter, $options);
}

function find_one(string $collectionName, array $filter = [], array $options = [])
{
    return collection(__get_dbname_or_throw(), $collectionName)->findOne($filter, $options);
}

function insert_many(string $collectionName, array $documents, array $options = []): \MongoDB\InsertManyResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->insertMany($documents, $options);
}

function insert_one(string $collectionName, $document, array $options = []): \MongoDB\InsertOneResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->insertOne($document, $options);
}

function update_one(string $collectionName, array $filter, $update, array $options = []): \MongoDB\UpdateResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->updateOne($filter, $update, $options);
}

function update_many(string $collectionName, array $filter, $update, array $options = []): \MongoDB\UpdateResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->updateMany($filter, $update, $filter);
}

function delete_one(string $collectionName, array $filter, array $options = []): \MongoDB\DeleteResult
{
    return colletion(__get_dbname_or_throw(), $collectionName)->deleteOne($filter, $options);
}

function delete_many(string $collectionName, array $filter, array $options = []): \MongoDB\DeleteResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->deleteMany($filter, $options);
}

function __get_dbname_or_throw(): string
{
    $databaseName = Container\get(MONGODB_USING_DBNAME);

    if (is_null($databaseName)) {
        throw new \UnderflowException('No Mongo Database name set');
    }

    return $databaseName;
}
