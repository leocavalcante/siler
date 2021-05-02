<?php declare(strict_types=1);

namespace Siler\Ratchet;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsConnection;
use Siler\GraphQL\SubscriptionsManager;
use SplObjectStorage;
use const Siler\GraphQL\WEBSOCKET_SUB_PROTOCOL;

/**
 * Class GraphQLSubscriptionsServer
 * @package Siler\Ratchet
 */
class GraphQLSubscriptionsServer implements MessageComponentInterface, WsServerInterface
{
    /** @var SubscriptionsManager */
    private $manager;
    /** @var SplObjectStorage<ConnectionInterface, SubscriptionsConnection> */
    private $connections;

    /**
     * SubscriptionsServer constructor.
     *
     * @param SubscriptionsManager $manager
     */
    public function __construct(SubscriptionsManager $manager)
    {
        $this->manager = $manager;
        /** @var SplObjectStorage<ConnectionInterface, SubscriptionsConnection> connections */
        $this->connections = new SplObjectStorage();
    }

    /**
     * @override
     * @param ConnectionInterface $conn
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->connections->offsetSet($conn, new GraphQLSubscriptionsConnection($conn, uniqid()));
    }

    /**
     * @override
     * @param ConnectionInterface $from
     * @param string $msg
     * @return void
     * @throws Exception
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $from = $this->connections->offsetGet($from);
        /** @var array<string, mixed> $msg */
        $msg = Json\decode(strval($msg));
        $this->manager->handle($from, $msg);
    }

    /**
     * @override
     * @param ConnectionInterface $conn     *
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->connections->offsetUnset($conn);
    }

    /**
     * @override
     * @param ConnectionInterface $conn
     * @param Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
    }

    /**
     * @return array<int, string>
     */
    public function getSubProtocols(): array
    {
        return [WEBSOCKET_SUB_PROTOCOL];
    }

    /**
     * @param ConnectionInterface $conn
     * @return SubscriptionsConnection
     */
    public function getSubscriptionsConnection(ConnectionInterface $conn): SubscriptionsConnection
    {
        return $this->connections->offsetGet($conn);
    }
}
