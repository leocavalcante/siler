<?php declare(strict_types=1);

namespace Siler\Ratchet;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServerInterface;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsManager;

class GraphQLSubscriptionsServer implements MessageComponentInterface, WsServerInterface
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
     * @param string $message
     *
     * @throws Exception
     */
    public function onMessage(ConnectionInterface $conn, $message)
    {
        $conn = new GraphQLSubscriptionsConnection($conn);
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
        return ['graphql-ws'];
    }
}
