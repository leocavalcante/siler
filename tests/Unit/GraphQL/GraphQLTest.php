<?php declare(strict_types=1);

namespace Siler\Test\Unit;

use GraphQL\Error\Debug;
use GraphQL\Error\Error;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\GraphQL;

class GraphQLTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testInit()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_POST = ['query' => '{ foo }'];

        $root = GraphQL\type('Root')([
            GraphQL\str('foo')(function ($root, $args) {
                return 'bar';
            })
        ]);

        $schema = new Schema(['query' => $root()]);

        GraphQL\init($schema);

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitJsonBody()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

        $root = GraphQL\type('Root')([
            GraphQL\str('foo')(function ($root, $args) {
                return 'bar';
            })
        ]);

        $schema = new Schema(['query' => $root()]);

        GraphQL\init($schema, null, null, __DIR__ . '/../../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitException()
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../../fixtures/graphql_error.json'));

        $_POST = ['query' => '{ foo }'];

        $root = GraphQL\type('Root')([
            GraphQL\str('foo')(function ($root, $args) {
                throw new Error('error_message');
            })
        ]);

        $schema = new Schema(['query' => $root()]);

        GraphQL\init($schema, null, null, __DIR__ . '/../../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    public function testSchema()
    {
        $typeDefs = file_get_contents(__DIR__ . '/../../fixtures/schema.graphql');
        $schema = GraphQL\schema($typeDefs);
        $this->assertInstanceOf(Schema::class, $schema);
    }

    /**
     * @runInSeparateProcess
     */
    public function testPromiseExecute()
    {
        $_POST = ['query' => '{ foo }'];

        $root = GraphQL\type('Root')([
            GraphQL\str('foo')(function ($root, $args) {
                return 'bar';
            })
        ]);

        $adapter = new SyncPromiseAdapter();
        $schema = new Schema(['query' => $root()]);
        $promise = GraphQL\promise_execute($adapter, $schema, GraphQL\input());
        $result = $adapter->wait($promise);

        $this->assertSame(['foo' => 'bar'], $result->data);
    }

    public function testDebug()
    {
        GraphQL\debug();
        $this->assertSame(Debug::INCLUDE_DEBUG_MESSAGE, Container\get(GraphQL\GRAPHQL_DEBUG));
        GraphQL\debug(0);
    }
}
