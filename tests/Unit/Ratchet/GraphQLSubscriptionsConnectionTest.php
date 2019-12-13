<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Ratchet;

use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Siler\Ratchet\GraphQLSubscriptionsConnection;

class GraphQLSubscriptionsConnectionTest extends TestCase
{
    public function testKey()
    {
        $interface = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $conn = new GraphQLSubscriptionsConnection($interface, 'test_key');
        $this->assertSame('test_key', $conn->key());
    }

    public function testSend()
    {
        $interface = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $interface->expects($this->once())
            ->method('send')
            ->with('test_data');

        $conn = new GraphQLSubscriptionsConnection($interface, 'test_key');
        $conn->send('test_data');
    }
}
