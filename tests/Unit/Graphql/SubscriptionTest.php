<?php

namespace Siler\Test\Unit\Graphql;

use Ratchet\ConnectionInterface;
use Siler\Graphql\Subscriber;
use Siler\Graphql\Subscription;

class SubscriptionTest extends \PHPUnit\Framework\TestCase
{
    public function testSubscribe()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $subscriber = new Subscriber('test123', 1, $conn);

        $subscription = new Subscription('test', '{ test }');
        $subscription->subscribe($subscriber);

        $this->assertArrayHasKey('test123', $subscription->subscribers);
        $this->assertContains($subscriber, $subscription->subscribers);
    }

    public function testUnsubscribe()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();
        $subscriber = new Subscriber('test123', 1, $conn);

        $subscription = new Subscription('test', '{ test }');
        $subscription->subscribe($subscriber);

        $this->assertArrayHasKey('test123', $subscription->subscribers);
        $this->assertContains($subscriber, $subscription->subscribers);

        $subscription->unsubscribe($subscriber);

        $this->assertArrayNotHasKey('test123', $subscription->subscribers);
        $this->assertNotContains($subscriber, $subscription->subscribers);
    }

    public function testBroadcast()
    {
        $message = 'test';

        $builder = $this->getMockBuilder(Subscriber::class);

        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $sub1 = $builder->setConstructorArgs(['test123', 1, $conn])
                        ->getMock();

        $sub2 = $builder->setConstructorArgs(['test456', 1, $conn])
                        ->getMock();

        $sub1->expects($this->once())
             ->method('emit')
             ->with($this->equalTo('test'));

        $sub2->expects($this->once())
             ->method('emit')
             ->with($this->equalTo('test'));

        $subscription = new Subscription('test', '{ test }');
        $subscription->subscribe($sub1);
        $subscription->subscribe($sub2);

        $subscription->broadcast($message);
    }
}
