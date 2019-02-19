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

/**
 * Calls `query()` and `fetchAll()` on the given `\PDOStatement` in a single function.
 *
 * @param string $statement The SQL statement.
 * @param int $fetchStyle The PDO fetch style to use, defaults to FETCH_ASSOC.
 * @param string $pdoName The PDO name on the `Siler\Container` to be used, defaults to the DB_DEFAULT_NAME.
 *
 * @return array|null Returns the resulting array (maybe empty) or null in case of error.
 */
function fetch_all(string $statement, int $fetchStyle = \PDO::FETCH_ASSOC, string $pdoName = DB_DEFAULT_NAME): ?array
{
    $results = query($statement, $pdoName)->fetchAll($fetchStyle);

    if ($results === false) {
        return null;
    }

    return $results;
}

/**
 * Gets a MySQL Data Source Name (DSN) composed by the given $opts.
 *
 * @param array $opts
 *
 * @return string
 */
function mysql_dsn(array $opts): string
{
    $defaults = [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => '',
        'charset' => 'utf8',
    ];

    $opts = array_merge($defaults, $opts);

    return "mysql:host={$opts['host']};port={$opts['port']};dbname={$opts['dbname']};charset={$opts['charset']}";
}
