<?php

declare(strict_types=1);

namespace Siler\Ratchet;

use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsConnection;

class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var ConnectionInterface */
    private $conn;
    /** @var string */
    private $key;

    public function __construct(ConnectionInterface $conn, string $key)
    {
        $this->conn = $conn;
        $this->key = $key;
    }

    public function send(string $data): void
    {
        $this->conn->send($data);
    }

    public function key(): string
    {
        return $this->key;
    }
}
