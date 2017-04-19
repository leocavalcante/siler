<?php

namespace Siler\Graphql;

use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\ConnectionInterface;

class SubscriptionServer implements MessageComponentInterface, WsServerInterface
{
    protected $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function onOpen(ConnectionInterface $conn)
    {

    }

    public function onMessage(ConnectionInterface $conn, $message)
    {

    }

    public function onClose(ConnectionInterface $conn)
    {

    }

    public function onError(ConnectionInterface $conn, \Exception $exception)
    {

    }

    public function getSubProtocols()
    {
        return ['graphql-subscriptions'];
    }
}
