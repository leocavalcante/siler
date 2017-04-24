<?php

namespace Siler\Test\Unit\Graphql;

use Ratchet\ConnectionInterface;
use Siler\Graphql\SubscriptionManager;
use Siler\Graphql\SubscriptionServer;

class SubscriptionServerTest extends \PHPUnit\Framework\TestCase
{
    public function testOnOpen()
    {
        $manager = $this->getMockBuilder(SubscriptionManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $server = new SubscriptionServer($manager);
        $server->onOpen($conn);

        $this->assertInstanceOf(SubscriptionServer::class, $server);
    }

    public function testOnMessage()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $manager = $this->getMockBuilder(SubscriptionManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $server = new SubscriptionServer($manager);

        $manager->expects($this->once())
                ->method('handleInit')
                ->with($conn);

        $message = '{"type": "init"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'subscription_start'];
        $manager->expects($this->once())
                ->method('handleSubscriptionStart')
                ->with($conn, $data);

        $message = '{"type": "subscription_start"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'subscription_data'];
        $manager->expects($this->once())
                ->method('handleSubscriptionData')
                ->with($data);

        $message = '{"type": "subscription_data"}';
        $server->onMessage($conn, $message);

        $data = ['type' => 'subscription_end'];
        $manager->expects($this->once())
                ->method('handleSubscriptionEnd')
                ->with($conn, $data);

        $message = '{"type": "subscription_end"}';
        $server->onMessage($conn, $message);
    }

    public function testOnClose()
    {
        $manager = $this->getMockBuilder(SubscriptionManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $server = new SubscriptionServer($manager);
        $server->onClose($conn);

        $this->assertInstanceOf(SubscriptionServer::class, $server);
    }

    public function testOnError()
    {
        $manager = $this->getMockBuilder(SubscriptionManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $server = new SubscriptionServer($manager);
        $server->onError($conn, new \Exception());

        $this->assertInstanceOf(SubscriptionServer::class, $server);
    }

    public function testGetSubProtocols()
    {
        $manager = $this->getMockBuilder(SubscriptionManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $server = new SubscriptionServer($manager);

        $this->assertContains('graphql-subscriptions', $server->getSubProtocols());
    }
}
