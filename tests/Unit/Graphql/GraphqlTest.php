<?php

namespace Siler\Test\Unit;

use GraphQL\Schema;
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
use Siler\Graphql;

class GraphqlTest extends \PHPUnit\Framework\TestCase
{
    public function testVal()
    {
        $fooVal = Graphql\val('FOO');
        $fooVal = $fooVal(4);

        $this->assertSame(4, $fooVal['value']);
    }

    public function testEnum()
    {
        $newhope = Graphql\val('NEWHOPE', 'Released in 1977.');
        $empire = Graphql\val('EMPIRE', 'Released in 1980.');
        $jedi = Graphql\val('JEDI', 'Released in 1983.');

        $enumType = Graphql\enum('Episode');
        $enumType = $enumType([
            $newhope(4),
            $empire(5),
            $jedi(6),
        ]);

        $this->assertInstanceOf(EnumType::class, $enumType);
    }

    public function testStr()
    {
        $field = Graphql\str('test');
        $field = $field();

        $this->assertInstanceOf(StringType::class, $field['type']);
    }

    public function testInt()
    {
        $field = Graphql\int('test');
        $field = $field();

        $this->assertInstanceOf(IntType::class, $field['type']);
    }

    public function testFloat()
    {
        $field = Graphql\float('test');
        $field = $field();

        $this->assertInstanceOf(FloatType::class, $field['type']);
    }

    public function testBool()
    {
        $field = Graphql\bool('test');
        $field = $field();

        $this->assertInstanceOf(BooleanType::class, $field['type']);
    }

    public function testId()
    {
        $field = Graphql\id('test');
        $field = $field();

        $this->assertInstanceOf(IDType::class, $field['type']);
    }

    public function testListOf()
    {
        $field = Graphql\list_of(Type::int(), 'test');
        $field = $field();

        $this->assertInstanceOf(ListOfType::class, $field['type']);
    }

    public function testInterface()
    {
        $id = Graphql\str('id', 'The id of the character.');
        $name = Graphql\str('id', 'The id of the character.');

        $interfaceType = Graphql\itype('Character', 'A character in the Star Wars Trilogy');
        $interfaceType = $interfaceType([$id(), $name()]);
        $interfaceType = $interfaceType(function ($obj) {
            return null;
        });

        $this->assertInstanceOf(InterfaceType::class, $interfaceType);
    }

    public function testObjectType()
    {
        $objectType = Graphql\type('Human', 'A humanoid creature in the Star Wars universe.');
        $objectType = $objectType([
            Graphql\str('id', 'The id of the human.'),
        ]);
        $objectType = $objectType();

        $this->assertInstanceOf(ObjectType::class, $objectType);
    }

    /**
     * @runInSeparateProcess
     */
    public function testInit()
    {
        $this->expectOutputString('{"data":{"foo":"bar"}}');

        $_POST = ['query' => '{ foo }'];

        $foo = Graphql\str('foo');

        $root = Graphql\type('Root');
        $root = $root([
            $foo(function ($root, $args) {
                return 'bar';
            }),
        ]);

        $schema = new Schema(['query' => $root()]);

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

        $foo = Graphql\str('foo');

        $root = Graphql\type('Root');
        $root = $root([
            $foo(function ($root, $args) {
                return 'bar';
            }),
        ]);

        $schema = new Schema(['query' => $root()]);

        Graphql\init($schema, null, null, __DIR__.'/../../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testInitException()
    {
        $this->expectOutputString(file_get_contents(__DIR__.'/../../fixtures/graphql_error.json'));

        $_POST = ['query' => '{ foo }'];

        $foo = Graphql\str('foo');

        $root = Graphql\type('Root');
        $root = $root([
            $foo(function ($root, $args) {
                throw new \Exception('error_message');
            }),
        ]);

        $schema = new Schema(['query' => $root()]);

        Graphql\init($schema, null, null, __DIR__.'/../../fixtures/graphql_input.json');

        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    public function testFieldResolveString()
    {
        $field = Graphql\field(new ObjectType(['name' => 'test']), 'test');
        $field = $field('stdClass');
        $computed = $field['resolve']();

        $this->assertInstanceOf(\stdClass::class, $computed);
    }

    public function testSchema()
    {
        $typeDefs = file_get_contents(__DIR__.'/../../fixtures/schema.graphql');
        $schema = Graphql\schema($typeDefs);
        $this->assertInstanceOf(Schema::class, $schema);
    }
}
