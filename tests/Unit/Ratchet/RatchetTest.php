<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Siler\Container;
use Siler\Ratchet;

class RatchetTest extends \PHPUnit\Framework\TestCase
{
    public function testInit()
    {
        $server = Ratchet\init(8888);

        $this->assertInstanceOf(\SplObjectStorage::class, Container\get(Ratchet\RATCHET_CONNECTIONS));
        $this->assertInstanceOf(IoServer::class, $server);
    }

    public function testConnected()
    {
        $expected = function () {
        };

        Ratchet\connected($expected);
        $actual = Container\get(Ratchet\RATCHET_EVENT_OPEN);

        $this->assertSame($expected, $actual);
    }

    public function testInbox()
    {
        $expected = function () {
        };

        Ratchet\inbox($expected);
        $actual = Container\get(Ratchet\RATCHET_EVENT_MESSAGE);

        $this->assertSame($expected, $actual);
    }

    public function testClosed()
    {
        $expected = function () {
        };

        Ratchet\closed($expected);
        $actual = Container\get(Ratchet\RATCHET_EVENT_CLOSE);

        $this->assertSame($expected, $actual);
    }

    public function testError()
    {
        $expected = function () {
        };

        Ratchet\error($expected);
        $actual = Container\get(Ratchet\RATCHET_EVENT_ERROR);

        $this->assertSame($expected, $actual);
    }

    public function testBroadcast()
    {
        $message = 'test';

        $mock = $this->getMockBuilder(ConnectionInterface::class)
                     ->setMethods(['send', 'close'])
                     ->getMock();

        $mock->expects($this->once())
             ->method('send')
             ->with($this->equalTo($message));

        Container\set(Ratchet\RATCHET_CONNECTIONS, [$mock]);

        Ratchet\broadcast($message);
    }

    public function testBroadcastIgnoreSender()
    {
        $message = 'test';

        $mock = $this->getMockBuilder(ConnectionInterface::class)
                     ->setMethods(['send', 'close'])
                     ->getMock();

        $mock->expects($this->never())
             ->method('send')
             ->with($this->equalTo($message));

        Container\set(Ratchet\RATCHET_CONNECTIONS, [$mock]);

        Ratchet\broadcast($message, $mock);
    }
}
