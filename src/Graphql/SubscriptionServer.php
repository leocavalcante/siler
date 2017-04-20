<?php

namespace Siler\Graphql;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\Graphql;

class SubscriptionServer implements MessageComponentInterface, WsServerInterface
{
    public function __construct(SubscriptionManager $manager)
    {
        $this->manager = $manager;
    }

    public function onOpen(ConnectionInterface $conn)
    {
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        $data = json_decode($message, true);

        switch ($data['type']) {
            case Graphql\INIT:
                return $this->manager->handleInit($conn);

            case Graphql\SUBSCRIPTION_START:
                return $this->manager->handleSubscriptionStart($conn, $data);

            case Graphql\SUBSCRIPTION_DATA:
                return $this->manager->handleSubscriptionData($data);

            case Graphql\SUBSCRIPTION_END:
                return $this->manager->handleSubscriptionEnd($conn, $data);
        }
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
