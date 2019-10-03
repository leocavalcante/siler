<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Ratchet;

use Exception;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsManager;
use Siler\GraphQL\SubscriptionsServer;
use Siler\Ratchet\GraphQLSubscriptionsServer;

use const Siler\GraphQL\WEBSOCKET_SUB_PROTOCOL;

class GraphQLSubscriptionsServerTest extends TestCase
{
    public function testOnOpen()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $server = new GraphQLSubscriptionsServer($manager);
        $server->onOpen($conn);

        $this->assertInstanceOf(GraphQLSubscriptionsServer::class, $server);
    }

    public function testOnMessage()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = new GraphQLSubscriptionsServer($manager);
        $server->onOpen($conn);

        $manager
            ->expects($this->exactly(5))
            ->method('handle');

        $server->onMessage($conn, '{"type": "connection_init"}');
        $server->onMessage($conn, '{"type": "start"}');
        $server->onMessage($conn, '{"type": "data"}');
        $server->onMessage($conn, '{"type": "stop"}');
        $server->onMessage($conn, '{"type": "unknown"}');
    }

    public function testOnClose()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $server = new GraphQLSubscriptionsServer($manager);
        $server->onClose($conn);

        $this->assertInstanceOf(GraphQLSubscriptionsServer::class, $server);
    }

    public function testOnError()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $server = new GraphQLSubscriptionsServer($manager);
        $server->onError($conn, new Exception());

        $this->assertInstanceOf(GraphQLSubscriptionsServer::class, $server);
    }

    public function testGetSubProtocols()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = new GraphQLSubscriptionsServer($manager);

        $this->assertContains(WEBSOCKET_SUB_PROTOCOL, $server->getSubProtocols());
    }
}
