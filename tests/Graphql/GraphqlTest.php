<?php

namespace Siler\Test;

use Siler\Graphql;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Schema;

class GraphqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testInit()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_POST = [
            'query' => '{ foo }',
        ];

        $rootQuery = new ObjectType([
            'name' => 'RootQuery',
            'fields' => [
                'foo' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                        return 'bar';
                    },
                ],
            ],
        ]);

        $schema = new Schema([
            'query' => $rootQuery,
        ]);

        Graphql\init($schema);

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitJsonBody()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

        $rootQuery = new ObjectType([
            'name' => 'RootQuery',
            'fields' => [
                'foo' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                        return 'bar';
                    },
                ],
            ],
        ]);

        $schema = new Schema([
            'query' => $rootQuery,
        ]);

        Graphql\init($schema, null, null, __DIR__.'/../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitException()
    {
        $this->expectOutputString(file_get_contents(__DIR__.'/../fixtures/graphql_error.json'));

        $_POST = [
            'query' => '{ foo }',
        ];

        $rootQuery = new ObjectType([
            'name' => 'RootQuery',
            'fields' => [
                'foo' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                        throw new \Exception('error_message');
                    },
                ],
            ],
        ]);

        $schema = new Schema([
            'query' => $rootQuery,
        ]);

        Graphql\init($schema, null, null, __DIR__.'/../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }
}
