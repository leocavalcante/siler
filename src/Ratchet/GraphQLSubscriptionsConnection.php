<?php declare(strict_types=1);

namespace Siler\Ratchet;

use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsConnection;

class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var ConnectionInterface */
    private $conn;

    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    public function send(string $data)
    {
        $this->conn->send($data);
    }
}
