<?php

namespace Siler\Test;

use Siler\Container;
use Siler\Ws;

use Ratchet\ConnectionInterface;

class WsTest extends \PHPUnit\Framework\TestCase
{
    public function testOn()
    {
        $expected = function () {
        };

        Ws\on('test', $expected);
        $actual = Container\get('ws_on_test');

        $this->assertSame($expected, $actual);
    }

    public function testOnOpen()
    {
        $expected = function () {
        };

        Ws\onopen($expected);
        $actual = Container\get('ws_on_open');

        $this->assertSame($expected, $actual);
    }

    public function testOnMessage()
    {
        $expected = function () {
        };

        Ws\onmessage($expected);
        $actual = Container\get('ws_on_message');

        $this->assertSame($expected, $actual);
    }

    public function testOnClose()
    {
        $expected = function () {
        };

        Ws\onclose($expected);
        $actual = Container\get('ws_on_close');

        $this->assertSame($expected, $actual);
    }

    public function testOnError()
    {
        $expected = function () {
        };

        Ws\onerror($expected);
        $actual = Container\get('ws_on_error');

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

        Container\set('ws_clients', [$mock]);

        Ws\broadcast($message);
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

        Container\set('ws_clients', [$mock]);

        Ws\broadcast($message, $mock);
    }
}
