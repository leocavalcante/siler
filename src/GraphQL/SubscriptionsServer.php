<?php

namespace Siler\GraphQL;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\GraphQL;

class SubscriptionsServer implements MessageComponentInterface, WsServerInterface
{
    /**
     * @var SubscriptionsManager
     */
    protected $manager;

    public function __construct(SubscriptionsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return void
     */
    public function onOpen(ConnectionInterface $conn)
    {
    }

    /**
     * @return void
     */
    public function onMessage(ConnectionInterface $conn, $message)
    {
        $data = json_decode($message, true);

        switch ($data['type']) {
            case GraphQL\GQL_CONNECTION_INIT:
                $this->manager->handleConnectionInit($conn, $data);
                break;

            case GraphQL\GQL_START:
                $this->manager->handleStart($conn, $data);
                break;

            case GraphQL\GQL_DATA:
                $this->manager->handleData($data);
                break;

            case GraphQL\GQL_STOP:
                $this->manager->handleStop($conn, $data);
                break;
        }
    }

    /**
     * @return void
     */
    public function onClose(ConnectionInterface $conn)
    {
    }

    /**
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
    }

    public function getSubProtocols() : array
    {
        return ['graphql-ws'];
    }
}
