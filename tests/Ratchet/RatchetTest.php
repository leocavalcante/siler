<?php

namespace Siler\Test;

use Siler\Container;
use Siler\Ratchet;
use Siler\Ratchet\MessageComponent;
use Ratchet\ConnectionInterface;

class RatchetTest extends \PHPUnit\Framework\TestCase
{
    public function testConnected()
    {
        $expected = function () {
        };

        Ratchet\connected($expected);
        $actual = Container\get(MessageComponent::RATCHET_EVENT_OPEN);

        $this->assertSame($expected, $actual);
    }

    public function testInbox()
    {
        $expected = function () {
        };

        Ratchet\inbox($expected);
        $actual = Container\get(MessageComponent::RATCHET_EVENT_MESSAGE);

        $this->assertSame($expected, $actual);
    }

    public function testClosed()
    {
        $expected = function () {
        };

        Ratchet\closed($expected);
        $actual = Container\get(MessageComponent::RATCHET_EVENT_CLOSE);

        $this->assertSame($expected, $actual);
    }

    public function testError()
    {
        $expected = function () {
        };

        Ratchet\error($expected);
        $actual = Container\get(MessageComponent::RATCHET_EVENT_ERROR);

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

        Container\set(MessageComponent::RATCHET_CONNECTIONS, [$mock]);

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

        Container\set(MessageComponent::RATCHET_CONNECTIONS, [$mock]);

        Ratchet\broadcast($message, $mock);
    }
}
