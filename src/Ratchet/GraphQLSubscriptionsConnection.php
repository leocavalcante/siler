<?php

declare(strict_types=1);

namespace Siler\Ratchet;

use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsConnection;

/**
 * Class GraphQLSubscriptionsConnection
 * @package Siler\Ratchet
 */
class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var ConnectionInterface */
    private $conn;
    /** @var string */
    private $key;

    /**
     * GraphQLSubscriptionsConnection constructor.
     * @param ConnectionInterface $conn
     * @param string $key
     */
    public function __construct(ConnectionInterface $conn, string $key)
    {
        $this->conn = $conn;
        $this->key = $key;
    }

    /**
     * @param string $data
     */
    public function send(string $data): void
    {
        $this->conn->send($data);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }
}
