<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

/*
 * Module to work with MongoDB operations.
 */

namespace Siler\Mongo;

use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\InsertManyResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use OutOfRangeException;
use Siler\Container;
use UnderflowException;

const MONGODB_DEFAULT_NAME = 'mongodb';
const MONGODB_USING_DBNAME = 'mongodb_dbname';

/**
 * Creates a new MongoDB\Client instance.
 *
 * @param string $uri
 * @param array $uriOptions
 * @param array $driverOptions
 * @param string $clientName
 *
 * @return Client
 */
function connect(
    string $uri = 'mongodb://127.0.0.1/',
    array $uriOptions = [],
    array $driverOptions = [],
    string $clientName = MONGODB_DEFAULT_NAME
): Client {
    $client = new Client($uri, $uriOptions, $driverOptions);
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
 * @return Database
 */
function database(
    string $databaseName,
    array $options = [],
    string $clientName = MONGODB_DEFAULT_NAME
): Database {
    if (!Container\has($clientName)) {
        throw new OutOfRangeException("$clientName not found");
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
 * @return Collection
 */
function collection(
    string $databaseName,
    string $collectionName,
    array $options = [],
    string $clientName = MONGODB_DEFAULT_NAME
): Collection {
    if (!Container\has($clientName)) {
        throw new OutOfRangeException("$clientName not found");
    }

    return Container\get($clientName)->selectCollection($databaseName, $collectionName, $options);
}

/**
 * Sugar to create a new ObjectId.
 *
 * @param string $oid
 *
 * @return ObjectId
 */
function oid(string $oid): ObjectId
{
    return new ObjectId($oid);
}

/**
 * Sets a default database name.
 *
 * @param string $databaseName
 */
function using(string $databaseName)
{
    Container\set(MONGODB_USING_DBNAME, $databaseName);
}

/**
 * Find operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param array $options
 *
 * @return Cursor
 */
function find(string $collectionName, array $filter = [], array $options = []): Cursor
{
    return collection(__get_dbname_or_throw(), $collectionName)->find($filter, $options);
}

/**
 * Find one operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param array $options
 *
 * @return array|object|null
 */
function find_one(string $collectionName, array $filter = [], array $options = [])
{
    return collection(__get_dbname_or_throw(), $collectionName)->findOne($filter, $options);
}

/**
 * Insert many operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param array $documents
 * @param array $options
 *
 * @return InsertManyResult
 */
function insert_many(string $collectionName, array $documents, array $options = []): InsertManyResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->insertMany($documents, $options);
}

/**
 * Insert one operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param mixed $document
 * @param array $options
 *
 * @return InsertOneResult
 */
function insert_one(string $collectionName, $document, array $options = []): InsertOneResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->insertOne($document, $options);
}

/**
 * Update one operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param mixed $update
 * @param array $options
 *
 * @return UpdateResult
 */
function update_one(string $collectionName, array $filter, $update, array $options = []): UpdateResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->updateOne($filter, $update, $options);
}

/**
 * Update many operation on the default database and in the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param mixed $update
 * @param array $options
 *
 * @return UpdateResult
 */
function update_many(string $collectionName, array $filter, $update, array $options = []): UpdateResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->updateMany($filter, $update, $options);
}

/**
 * Delete one operation on the default database and on the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param array $options
 *
 * @return DeleteResult
 */
function delete_one(string $collectionName, array $filter, array $options = []): DeleteResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->deleteOne($filter, $options);
}

/**
 * Delete many operation on the default database and on the given collection.
 *
 * @param string $collectionName
 * @param array $filter
 * @param array $options
 *
 * @return DeleteResult
 */
function delete_many(string $collectionName, array $filter, array $options = []): DeleteResult
{
    return collection(__get_dbname_or_throw(), $collectionName)->deleteMany($filter, $options);
}

/**
 * @return string
 * @internal Gets the default database name or throws an Exception.
 *
 */
function __get_dbname_or_throw(): string
{
    $databaseName = Container\get(MONGODB_USING_DBNAME);

    if (is_null($databaseName)) {
        throw new UnderflowException('No Mongo Database name set');
    }

    return $databaseName;
}
