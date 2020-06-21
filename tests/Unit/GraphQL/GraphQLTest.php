<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

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

        $schema = GraphQL\schema('type Query { foo: String }', ['Query' => ['foo' => 'bar']]);

        GraphQL\init($schema);

        if (function_exists('xdebug_get_headers')) {
            $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitJsonBody()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

        $schema = GraphQL\schema('type Query { foo: String }', ['Query' => ['foo' => 'bar']]);

        GraphQL\init($schema, null, null, __DIR__ . '/../../fixtures/graphql_input.json');

        if (function_exists('xdebug_get_headers')) {
            $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitException()
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../../fixtures/graphql_error.json'));

        $_POST = ['query' => '{ foo }'];

        $schema = GraphQL\schema('type Query { foo: String }', ['Query' => ['foo' => function () {
            throw new Error('error_message');
        }]]);

        GraphQL\init($schema, null, null, __DIR__ . '/../../fixtures/graphql_input.json');

        if (function_exists('xdebug_get_headers')) {
            $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
        }
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

        $schema = GraphQL\schema('type Query { foo: String }', ['Query' => ['foo' => 'bar']]);
        $adapter = new SyncPromiseAdapter();
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

    public function testDebugging()
    {
        $this->assertSame(0, GraphQL\debugging());
        GraphQL\debug();
        $this->assertSame(1, GraphQL\debugging());
        GraphQL\debug(0);
        $this->assertSame(0, GraphQL\debugging());
    }
}
