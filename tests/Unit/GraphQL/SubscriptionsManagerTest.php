<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Graphql;

use GraphQL\Executor\Executor;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Ratchet\ConnectionInterface;
use Siler\GraphQL\SubscriptionsManager;

class SubscriptionsManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleConnectionInit()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $message = '{"type":"connection_ack","payload":[]}';

        $conn
            ->expects($this->once())
            ->method('send')
            ->with($message);

        $schema = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = new SubscriptionsManager($schema);
        $manager->handleConnectionInit($conn);
    }

    public function testHandleStartQuery()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $dataResponse = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';
        $completeResponse = '{"type":"complete","id":1}';

        $schema = BuildSchema::build(
            '
            type Query {
                dummy: String
            }
        '
        );

        Executor::setDefaultFieldResolver(function ($root) {
            return 'test';
        });

        $data = [
            'id' => 1,
            'payload' => ['query' => 'query { dummy }']
        ];

        $manager = new SubscriptionsManager($schema);
        $manager->handleConnectionInit($conn);

        $conn
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$dataResponse], [$completeResponse]);

        $manager->handleStart($conn, $data);
    }

    public function testHandleStartMutation()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $dataResponse = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';
        $completeResponse = '{"type":"complete","id":1}';

        $schema = BuildSchema::build(
            '
            type Query {
                dummy: String
            }

            type Mutation {
                dummy: String
            }
        '
        );

        Executor::setDefaultFieldResolver(function ($root) {
            return 'test';
        });

        $data = [
            'id' => 1,
            'payload' => ['query' => 'mutation { dummy }']
        ];

        $manager = new SubscriptionsManager($schema);
        $manager->handleConnectionInit($conn);

        $conn
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$dataResponse], [$completeResponse]);

        $manager->handleStart($conn, $data);
    }

    public function testHandleStartSubscription()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $dataResponse = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';

        $schema = BuildSchema::build(
            '
            type Query {
                dummy: String
            }

            type Subscription {
                dummy: String
            }
        '
        );

        Executor::setDefaultFieldResolver(function ($root) {
            return 'test';
        });

        $data = [
            'id' => 1,
            'payload' => ['query' => 'subscription { dummy }']
        ];

        $manager = new SubscriptionsManager($schema);
        $manager->handleConnectionInit($conn);

        $conn
            ->expects($this->once())
            ->method('send')
            ->with($dataResponse);

        $manager->handleStart($conn, $data);
    }

    public function testHandleStartFail()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $response = '{"type":"error","id":1,"payload":"Missing query parameter from payload"}';
        $complete = '{"type":"complete","id":1}';

        $conn
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$response], [$complete]);

        $schema = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = [
            'id' => 1,
            'payload' => []
        ];

        $manager = new SubscriptionsManager($schema);
        $manager->handleStart($conn, $data);
    }

    public function testHandleData()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $schema = BuildSchema::build(
            '
            type Query {
                dummy: String
            }

            type Subscription {
                dummy: String
            }
        '
        );

        Executor::setDefaultFieldResolver(function ($root) {
            return 'test';
        });

        $startData = [
            'id' => 1,
            'payload' => ['query' => 'subscription { dummy }']
        ];

        $data = [
            'subscription' => 'dummy',
            'payload' => 'test'
        ];

        $startExpected = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';
        $expected = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';

        $conn
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$startExpected], [$expected]);

        $manager = new SubscriptionsManager($schema);
        $manager->handleStart($conn, $startData);
        $manager->handleData($data);
    }

    public function testHandleNullData()
    {
        $schema = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = ['subscription' => 'doNotExists'];

        $manager = new SubscriptionsManager($schema);
        $this->assertNull($manager->handleData($data));
    }

    public function testHandleDataWithFilters()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $schema = BuildSchema::build(
            '
            type Query {
                dummy: String
            }

            type Subscription {
                dummy: String
            }
        '
        );

        Executor::setDefaultFieldResolver(function ($root) {
            return 'test';
        });

        $startData = [
            'id' => 1,
            'payload' => [
                'query' => 'subscription { dummy }',
                'variables' => ['pass' => 'bar']
            ]
        ];

        $data = [
            'subscription' => 'dummy',
            'payload' => 'foo'
        ];

        $expected = '{"type":"data","id":1,"payload":{"data":{"dummy":"test"}}}';

        $filters = [
            'test' => function ($payload, $vars) {
                return $payload == $vars['pass'];
            }
        ];

        $conn
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive([$expected], [$expected]);

        $manager = new SubscriptionsManager($schema, $filters);
        $manager->handleStart($conn, $startData);
        $manager->handleData($data);

        $data = [
            'subscription' => 'test',
            'payload' => 'bar'
        ];

        $manager->handleData($data);
    }

    public function testHandleStop()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)->getMock();

        $schema = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = [
            'id' => 1,
            'payload' => ['query' => 'subscription { test }']
        ];

        $manager = new SubscriptionsManager($schema);
        $manager->handleConnectionInit($conn);
        $manager->handleStart($conn, $data);
        $manager->handleStop($conn, ['id' => 1]);

        $this->assertEmpty($manager->getConnStorage()->offsetGet($conn));
        $this->assertTrue(empty($manager->getSubscriptions()['test']));
    }

    public function testGetSubscriptionName()
    {
        $document = $this->getMockBuilder(DocumentNode::class)
            ->disableOriginalConstructor()
            ->getMock();

        $schema = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nameNode = new \stdClass();
        $nameNode->value = 'test';

        $selection = new \stdClass();
        $selection->name = $nameNode;

        $selectionSet = new \stdClass();
        $selectionSet->selections = [$selection];

        $definition = new \stdClass();
        $definition->selectionSet = $selectionSet;

        $document->definitions = [$definition];

        $manager = new SubscriptionsManager($schema);

        $this->assertSame('test', $manager->getSubscriptionName($document));
    }
}
