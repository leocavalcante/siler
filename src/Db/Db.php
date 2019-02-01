<?php

declare(strict_types=1);

namespace Siler\Db;

use Siler\Container;

const DB_DEFAULT_NAME = 'db';

/**
 * Creates a new PDO instance.
 *
 * @param string $dsn
 * @param string $username
 * @param string $passwd
 * @param array  $options
 *
 * @return \PDO
 */
function connect(string $dsn, string $username = 'root', string $passwd = '', array $options = []): \PDO
{
    $pdo = new \PDO($dsn, $username, $passwd, $options);
    Container\set(DB_DEFAULT_NAME, $pdo);

    return $pdo;
}

/**
 * Query through a PDO instance.
 *
 * @param string $statement
 * @param string $pdoName
 *
 * @return \PDOStatement
 */
function query(string $statement, string $pdoName = DB_DEFAULT_NAME): \PDOStatement
{
    if (!Container\has($pdoName)) {
        throw new \OutOfRangeException("$pdoName not found");
    }

    return Container\get($pdoName)->query($statement);
}

/**
 * Prepare a statement.
 *
 * @param string $statement
 * @param array  $driverOpts
 * @param string $pdoName
 *
 * @return \PDOStatement|null
 */
function prepare(string $statement, array $driverOpts = [], string $pdoName = DB_DEFAULT_NAME): ?\PDOStatement
{
    if (!Container\has($pdoName)) {
        throw new \OutOfRangeException("$pdoName not found");
    }

    $stmt = Container\get($pdoName)->prepare($statement, $driverOpts);

    if ($stmt === false) {
        return null;
    }

    return $stmt;
}

/**
 * Gets error info about a PDO instance.
 *
 * @param string $pdoName
 *
 * @return array
 */
function error(string $pdoName = DB_DEFAULT_NAME): array
{
    if (!Container\has($pdoName)) {
        throw new \OutOfRangeException("$pdoName not found");
    }

    return Container\get($pdoName)->errorInfo();
}
