<?php

declare(strict_types=1);

namespace Siler\Ratchet;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsManager;
use SplObjectStorage;

use const Siler\GraphQL\WEBSOCKET_SUB_PROTOCOL;

class GraphQLSubscriptionsServer implements MessageComponentInterface, WsServerInterface
{
    /** @var SubscriptionsManager */
    private $manager;
    /** @var SplObjectStorage */
    private $connections;

    /**
     * SubscriptionsServer constructor.
     *
     * @param SubscriptionsManager $manager
     */
    public function __construct(SubscriptionsManager $manager)
    {
        $this->manager = $manager;
        $this->connections = new SplObjectStorage();
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
        $this->connections->offsetSet($conn, new GraphQLSubscriptionsConnection($conn, uniqid()));
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     * @param string $message
     *
     * @throws Exception
     */
    public function onMessage(ConnectionInterface $conn, $message)
    {
        $conn = $this->connections->offsetGet($conn);
        $message = Json\decode($message);
        $this->manager->handle($conn, $message);
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
        $this->connections->offsetUnset($conn);
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     * @param Exception $exception
     *
     * @suppress PhanUnusedPublicMethodParameter
     */
    public function onError(ConnectionInterface $conn, Exception $exception)
    {
    }

    /**
     * @return array
     */
    public function getSubProtocols(): array
    {
        return [WEBSOCKET_SUB_PROTOCOL];
    }
}
