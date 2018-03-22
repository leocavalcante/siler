<?php

namespace Siler\Graphql;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\Graphql;

class WsServer implements MessageComponentInterface, WsServerInterface
{
    /**
     * @var WsManager
     */
    protected $manager;

    public function __construct(WsManager $manager)
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
            case Graphql\GQL_CONNECTION_INIT:
                $this->manager->handleConnectionInit($conn);
                break;

            case Graphql\GQL_START:
                $this->manager->handleStart($conn, $data);
                break;

            case Graphql\GQL_DATA:
                $this->manager->handleData($data);
                break;

            case Graphql\GQL_STOP:
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
