<?php

namespace Siler\Test;

use Siler\Container;
use Siler\Ws\MessageComponent;
use Ratchet\ConnectionInterface;

class MessageComponentTest extends \PHPUnit\Framework\TestCase
{
    protected $conn;
    protected $storage;

    public function setUp()
    {
        $this->conn = $this->createMock(ConnectionInterface::class);
        $this->storage = $this->createMock(\SplObjectStorage::class);

        Container\set('ws_clients', $this->storage);
    }

    public function testOnOpen()
    {
        $this->storage->expects($this->once())
                ->method('attach')
                ->with($this->equalTo($this->conn));

        $onOpenCalled = false;

        $onOpen = function () use (&$onOpenCalled) {
            $onOpenCalled = true;
        };

        Container\set('ws_on_open', $onOpen);

        $messageComponent = new MessageComponent();
        $messageComponent->onOpen($this->conn);

        $this->assertTrue($onOpenCalled);
    }

    public function testOnMessage()
    {
        $message = 'test';

        $onMessageFrom = null;
        $onMessageMessage = null;

        $onMessage = function ($from, $message) use (&$onMessageFrom, &$onMessageMessage) {
            $onMessageFrom = $from;
            $onMessageMessage = $message;
        };

        Container\set('ws_on_message', $onMessage);

        $messageComponent = new MessageComponent();
        $messageComponent->onMessage($this->conn, $message);

        $this->assertSame($this->conn, $onMessageFrom);
        $this->assertEquals($message, $onMessageMessage);
    }

    /*public function testOnClose(ConnectionInterface $conn)
    {
        Container\get('ws_clients')->detach($conn);
        $this->callback('close', [$conn]);
    }

    public function testOnError(ConnectionInterface $conn, \Exception $e)
    {
        $this->callback('error', [$conn, $e]);
        $conn->close();
    }*/
}
