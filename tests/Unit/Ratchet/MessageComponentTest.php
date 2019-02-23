<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use Ratchet\ConnectionInterface;
use Siler\Container;
use Siler\Ratchet;
use Siler\Ratchet\MessageComponent;

class MessageComponentTest extends \PHPUnit\Framework\TestCase
{
    protected $conn;
    protected $storage;

    public function setUp(): void
    {
        $this->conn    = $this->createMock(ConnectionInterface::class);
        $this->storage = $this->createMock(\SplObjectStorage::class);

        Container\set(Ratchet\RATCHET_CONNECTIONS, $this->storage);
    }

    public function testCallbackIsNull()
    {
        $onOpenCalled = false;

        $messageComponent = new MessageComponent();
        $messageComponent->onOpen($this->conn);

        $this->assertFalse($onOpenCalled);
    }

    public function testCallbackIsntCallable()
    {
        $onOpenCalled = false;
        $onOpen       = 'not callable';

        Container\set(Ratchet\RATCHET_EVENT_OPEN, $onOpen);

        $messageComponent = new MessageComponent();
        $messageComponent->onOpen($this->conn);

        $this->assertFalse($onOpenCalled);
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

        Container\set(Ratchet\RATCHET_EVENT_OPEN, $onOpen);

        $messageComponent = new MessageComponent();
        $messageComponent->onOpen($this->conn);

        $this->assertTrue($onOpenCalled);
    }

    public function testOnMessage()
    {
        $message = 'test';

        $onMessageFrom    = null;
        $onMessageMessage = null;

        $onMessage = function ($from, $message) use (&$onMessageFrom, &$onMessageMessage) {
            $onMessageFrom    = $from;
            $onMessageMessage = $message;
        };

        Container\set(Ratchet\RATCHET_EVENT_MESSAGE, $onMessage);

        $messageComponent = new MessageComponent();
        $messageComponent->onMessage($this->conn, $message);

        $this->assertSame($this->conn, $onMessageFrom);
        $this->assertSame($message, $onMessageMessage);
    }

    public function testOnClose()
    {
        $this->storage->expects($this->once())
            ->method('detach')
            ->with($this->equalTo($this->conn));

        $onCloseCalled = false;

        $onClose = function () use (&$onCloseCalled) {
            $onCloseCalled = true;
        };

        Container\set(Ratchet\RATCHET_EVENT_CLOSE, $onClose);

        $messageComponent = new MessageComponent();
        $messageComponent->onClose($this->conn);

        $this->assertTrue($onCloseCalled);
    }

    public function testOnError()
    {
        $expectedException = new \Exception();
        $actualException   = null;

        $this->conn->expects($this->once())
            ->method('close');

        $onError = function ($conn, $e) use (&$actualException) {
            $actualException = $e;
        };

        Container\set(Ratchet\RATCHET_EVENT_ERROR, $onError);

        $messageComponent = new MessageComponent();
        $messageComponent->onError($this->conn, $expectedException);

        $this->assertSame($expectedException, $actualException);
    }
}
