<?php

namespace Siler\Test\Unit\Graphql;

use GraphQL\Executor\Executor;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Schema;
use GraphQL\Utils\BuildSchema;
use Ratchet\ConnectionInterface;
use Siler\Graphql\SubscriptionManager;

class SubscriptionManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleInit()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $message = '{"type":"init_success"}';

        $conn->expects($this->once())
             ->method('send')
             ->with($message);

        $schema = $this->getMockBuilder(Schema::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $manager = new SubscriptionManager($schema);
        $manager->handleInit($conn);
    }

    public function testHandleSubscriptionStart()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $response = '{"type":"subscription_success","id":1}';

        $schema = $this->getMockBuilder(Schema::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $data['query'] = 'subscription { test }';
        $data['id'] = 1;

        $manager = new SubscriptionManager($schema);
        $manager->handleInit($conn);

        $conn->expects($this->once())
             ->method('send')
             ->with($response);

        $manager->handleSubscriptionStart($conn, $data);
    }

    public function testHandleSubscriptionStartFail()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $response = '{"type":"subscription_fail","id":1,"payload":{"errors":[{"message":"Undefined index: query"}]}}';

        $conn->expects($this->once())
             ->method('send')
             ->with($response);

        $schema = $this->getMockBuilder(Schema::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        // $data['query'] = 'subscription { test }';
        $data['id'] = 1;

        $manager = new SubscriptionManager($schema);
        $manager->handleSubscriptionStart($conn, $data);
    }

    public function testHandleSubscriptionData()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $schema = BuildSchema::build('
            type Query {
                dummy: String
            }

            type Subscription {
                test: String
            }
        ');

        Executor::setDefaultFieldResolver(function ($root) {
            return $root;
        });

        $startData = [
            'id'    => 1,
            'query' => '
                subscription {
                    test
                }
            ',
        ];

        $data = [
            'subscription' => 'test',
            'payload'      => 'foo',
        ];

        $expected = '{"type":"subscription_data","payload":{"data":{"test":"foo"}},"id":1}';

        $manager = new SubscriptionManager($schema);
        $manager->handleSubscriptionStart($conn, $startData);

        $conn->expects($this->once())
             ->method('send')
             ->with($expected);

        $manager->handleSubscriptionData($data);
    }

    public function testHandleNullSubscriptionData()
    {
        $schema = $this->getMockBuilder(Schema::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $data = ['subscription' => 'doNotExists'];

        $manager = new SubscriptionManager($schema);
        $this->assertNull($manager->handleSubscriptionData($data));
    }

    public function testHandleSubscriptionDataWithFilters()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $schema = BuildSchema::build('
            type Query {
                dummy: String
            }

            type Subscription {
                test: String
            }
        ');

        Executor::setDefaultFieldResolver(function ($root) {
            return $root;
        });

        $startData = [
            'id'    => 1,
            'query' => '
                subscription {
                    test
                }
            ',
            'variables' => [
                'pass' => 'bar',
            ],
        ];

        $data = [
            'subscription' => 'test',
            'payload'      => 'foo',
        ];

        $expected = '{"type":"subscription_data","payload":{"data":{"test":"bar"}},"id":1}';

        $filters = [
            'test' => function ($payload, $vars) {
                return $payload == $vars['pass'];
            },
        ];

        $manager = new SubscriptionManager($schema, $filters);
        $manager->handleSubscriptionStart($conn, $startData);

        $conn->expects($this->once())
             ->method('send')
             ->with($expected);

        $manager->handleSubscriptionData($data);

        $data = [
            'subscription' => 'test',
            'payload'      => 'bar',
        ];

        $manager->handleSubscriptionData($data);
    }

    public function testHandleSubscriptionEnd()
    {
        $conn = $this->getMockBuilder(ConnectionInterface::class)
                     ->getMock();

        $schema = $this->getMockBuilder(Schema::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $data = [
            'id'    => 1,
            'query' => 'subscription { test }',
        ];

        $manager = new SubscriptionManager($schema);
        $manager->handleInit($conn);
        $manager->handleSubscriptionStart($conn, $data);
        $manager->handleSubscriptionEnd($conn, ['id' => 1]);

        $this->assertEmpty($manager->getConnSubStorage()->offsetGet($conn));
        $this->assertEmpty($manager->getSubscriptions()['test']);
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

        $manager = new SubscriptionManager($schema);

        $this->assertSame('test', $manager->getSubscriptionName($document));
    }
}
