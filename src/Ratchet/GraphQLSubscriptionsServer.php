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
     *
     * @return void
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
     * @return void
     * @throws Exception
     *
     */
    public function onMessage(ConnectionInterface $conn, $message)
    {
        /** @var SubscriptionsConnection $conn */
        $conn = $this->connections->offsetGet($conn);
        /** @var array<string, mixed> $message */
        $message = Json\decode(strval($message));
        $this->manager->handle($conn, $message);
    }

    /**
     * @override
     *
     * @param ConnectionInterface $conn
     *
     * @suppress PhanUnusedPublicMethodParameter
     *
     * @return void
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
     *
     * @return void
     */
    public function onError(ConnectionInterface $conn, Exception $exception)
    {
    }

    /**
     * @return string[]
     *
     * @psalm-return array{0: string}
     */
    public function getSubProtocols(): array
    {
        return [WEBSOCKET_SUB_PROTOCOL];
    }
}
