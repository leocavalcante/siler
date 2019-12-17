<?php

namespace Siler\Test\Unit\Ratchet;

use PHPUnit\Framework\TestCase;
use Ratchet\Server\IoServer;
use Siler\GraphQL\SubscriptionsManager;
use function Siler\Ratchet\graphql_subscriptions;

class RatchetTest extends TestCase
{
    public function testGraphQLSubscriptions()
    {
        $manager = $this->getMockBuilder(SubscriptionsManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = graphql_subscriptions($manager);
        $this->assertInstanceOf(IoServer::class, $server);
    }
}
