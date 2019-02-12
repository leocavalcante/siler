<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Graphql;

use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsManager;
use Siler\GraphQL\SubscriptionsServer;

class SubscriptionsServerTest extends \PHPUnit\Framework\TestCase
{


    public function testOnOpen()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $server = new SubscriptionsServer($manager);
        $server->onOpen($conn);

        $this->assertInstanceOf(SubscriptionsServer::class, $server);
    }


    public function testOnMessage()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = new SubscriptionsServer($manager);

        $manager->expects($this->once())
            ->method('handleConnectionInit')
            ->with($conn);

        $message = '{"type": "connection_init"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'start'];
        $manager->expects($this->once())
            ->method('handleStart')
            ->with($conn, $data);

        $message = '{"type": "start"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'data'];
        $manager->expects($this->once())
            ->method('handleData')
            ->with($data);

        $message = '{"type": "data"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'stop'];
        $manager->expects($this->once())
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

        $conn = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $server = new SubscriptionsServer($manager);
        $server->onClose($conn);

        $this->assertInstanceOf(SubscriptionsServer::class, $server);
    }


    public function testOnError()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();

        $server = new SubscriptionsServer($manager);
        $server->onError($conn, new \Exception());

        $this->assertInstanceOf(SubscriptionsServer::class, $server);
    }


    public function testGetSubProtocols()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = new SubscriptionsServer($manager);

        $this->assertContains('graphql-ws', $server->getSubProtocols());
    }
}
