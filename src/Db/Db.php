<?php

declare(strict_types=1);

namespace Siler\Db;

use Siler\Container;

const DB_DEFAULT_NAME = 'db';

function connect(string $dsn, string $username = 'root', string $passwd = '', array $options = []): \PDO
{
    $pdo = new \PDO($dsn, $username, $passwd, $options);
    Container\set(DB_DEFAULT_NAME, $pdo);
    return $pdo;
}

function query(string $statement, string $pdoName = DB_DEFAULT_NAME): \PDOStatement
{
    if (!Container\has($pdoName)) {
        throw new \OutOfRangeException("$pdoName not found");
    }

    return Container\get($pdoName)->query($statement);
}

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

function error(string $pdoName = DB_DEFAULT_NAME): array
{
    if (!Container\has($pdoName)) {
        throw new \OutOfRangeException("$pdoName not found");
    }

    return Container\get($pdoName)->errorInfo();
}
