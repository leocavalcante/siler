<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Siler\GraphQL;

class GraphQLTest extends \PHPUnit\Framework\TestCase
{


    public function testVal()
    {
        $fooVal = GraphQL\val('FOO')(4);
        $this->assertSame(4, $fooVal['value']);
    }


    public function testEnum()
    {
        $enumType = GraphQL\enum('Episode')(
            [
                GraphQL\val('NEWHOPE', 'Released in 1977.')(4),
                GraphQL\val('EMPIRE', 'Released in 1980.')(5),
                GraphQL\val('JEDI', 'Released in 1983.')(6),
            ]
        );

        $this->assertInstanceOf(EnumType::class, $enumType);
    }


    public function testStr()
    {
        $field = GraphQL\str('test')();
        $this->assertInstanceOf(StringType::class, $field['type']);
    }


    public function testInt()
    {
        $field = GraphQL\int('test')();
        $this->assertInstanceOf(IntType::class, $field['type']);
    }


    public function testFloat()
    {
        $field = GraphQL\float('test')();
        $this->assertInstanceOf(FloatType::class, $field['type']);
    }


    public function testBool()
    {
        $field = GraphQL\bool('test')();
        $this->assertInstanceOf(BooleanType::class, $field['type']);
    }


    public function testId()
    {
        $field = GraphQL\id('test')();
        $this->assertInstanceOf(IDType::class, $field['type']);
    }


    public function testListOf()
    {
        $field = GraphQL\list_of(Type::int(), 'test')();
        $this->assertInstanceOf(ListOfType::class, $field['type']);
    }


    public function testInterface()
    {
        $interfaceType = GraphQL\itype('Character', 'A character in the Star Wars Trilogy')(
            [
                GraphQL\str('id', 'The id of the character.')(),
                GraphQL\str('name', 'The name of the character.')(),
            ]
        )(
            function ($obj) {
                    return null;
            }
        );

        $this->assertInstanceOf(InterfaceType::class, $interfaceType);
    }


    public function testObjectType()
    {
        $objectType = GraphQL\type('Human', 'A humanoid creature in the Star Wars universe.')(
            [
                GraphQL\str('id', 'The id of the human.'),
            ]
        )();

        $this->assertInstanceOf(ObjectType::class, $objectType);
    }


    /**
     * @runInSeparateProcess
     */
    public function testInit()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_POST = ['query' => '{ foo }'];

        $root = GraphQL\type('Root')(
            [
                GraphQL\str('foo')(
                    function ($root, $args) {
                        return 'bar';
                    }
                ),
            ]
        );

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

        $root = GraphQL\type('Root')(
            [
                GraphQL\str('foo')(
                    function ($root, $args) {
                        return 'bar';
                    }
                ),
            ]
        );

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

        $root = GraphQL\type('Root')(
            [
                GraphQL\str('foo')(
                    function ($root, $args) {
                        throw new Error('error_message');
                    }
                ),
            ]
        );

        $schema = new Schema(['query' => $root()]);

        GraphQL\init($schema, null, null, __DIR__ . '/../../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }


    public function testFieldResolveString()
    {
        $field    = GraphQL\field(new ObjectType(['name' => 'test']), 'test')('stdClass');
        $computed = $field['resolve']();

        $this->assertInstanceOf(\stdClass::class, $computed);
    }


    public function testSchema()
    {
        $typeDefs = file_get_contents(__DIR__ . '/../../fixtures/schema.graphql');
        $schema   = GraphQL\schema($typeDefs);
        $this->assertInstanceOf(Schema::class, $schema);
    }
}
