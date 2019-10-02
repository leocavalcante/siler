<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Ratchet;

use Exception;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsConnection;
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
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = new GraphQLSubscriptionsServer($manager);
        $server->onOpen($conn);

        $message = '{"type": "connection_init"}';
        $manager
            ->expects($this->once())
            ->method('handle')
            ->with($conn);

        $server->onMessage($conn, $message);

        $data = ['type' => 'start'];
        $manager
            ->expects($this->once())
            ->method('handleStart')
            ->with($conn, $data);

        $message = '{"type": "start"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'data'];
        $manager
            ->expects($this->once())
            ->method('handleData')
            ->with($data);

        $message = '{"type": "data"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'stop'];
        $manager
            ->expects($this->once())
            ->method('handleStop')
            ->with($conn, $data);

        $message = '{"type": "stop"}';
        $server->onMessage($conn, $message);

        $message = '{"type": "unknown"}';
        $server->onMessage($conn, $message);
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
