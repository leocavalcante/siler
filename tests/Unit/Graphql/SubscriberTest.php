<?php

namespace Siler\Test\Unit\Graphql;

use Ratchet\ConnectionInterface;
use Siler\Graphql\Subscriber;
use Siler\Graphql\Subscription;

class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testEmit()
    {
        $actual = ['foo' => 'bar'];
        $expected = '{"foo":"bar","id":1}';

        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $conn->expects($this->once())
             ->method('send')
             ->with($this->equalTo($expected));

        $subscriber = new Subscriber('test123', 1, $conn);
        $subscriber->emit($actual);
    }

    public function testSubscribe()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $subscriber = new Subscriber('test123', 1, $conn);

        $subscription = $this->getMockBuilder(Subscription::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $subscription->expects($this->once())
                     ->method('subscribe')
                     ->with($this->equalTo($subscriber));

        $subscriber->subscribe($subscription);
    }

    public function testUnsubscribe()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $subscriber = new Subscriber('test123', 1, $conn);

        $subscription = $this->getMockBuilder(Subscription::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $subscription->expects($this->once())
                     ->method('unsubscribe')
                     ->with($this->equalTo($subscriber));

        $subscriber->subscribe($subscription);
        $subscriber->unsubscribe($subscription);
    }
}
