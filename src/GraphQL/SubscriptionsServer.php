<?php

declare(strict_types=1);

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

    /**
     * SubscriptionsServer constructor.
     *
     * @param SubscriptionsManager $manager
     */
    public function __construct(SubscriptionsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     *
     * @suppress PhanUnusedPublicMethodParameter
     */
    public function onOpen(ConnectionInterface $conn)
    {
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     * @param string              $message
     */
    public function onMessage(ConnectionInterface $conn, $message)
    {
        $data = json_decode($message, true);

        if (!is_array($data)) {
            throw new \UnexpectedValueException('GraphQL message should be a JSON object');
        }

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
     * @override
     *
     * @param ConnectionInterface $conn
     *
     * @suppress PhanUnusedPublicMethodParameter
     */
    public function onClose(ConnectionInterface $conn)
    {
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     * @param \Exception          $exception
     *
     * @suppress PhanUnusedPublicMethodParameter
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
    }

    /**
     * @return array
     */
    public function getSubProtocols(): array
    {
        return ['graphql-ws'];
    }
}
