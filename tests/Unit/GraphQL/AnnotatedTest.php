<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

use PHPUnit\Framework\TestCase;
use Siler\Test\Unit\GraphQL\Annotated\Bar;
use Siler\Test\Unit\GraphQL\Annotated\Enum;
use Siler\Test\Unit\GraphQL\Annotated\Foo;
use Siler\Test\Unit\GraphQL\Annotated\FooBar;
use Siler\Test\Unit\GraphQL\Annotated\IFoo;
use Siler\Test\Unit\GraphQL\Annotated\Input;
use Siler\Test\Unit\GraphQL\Annotated\ListOfException;
use Siler\Test\Unit\GraphQL\Annotated\Mutation;
use Siler\Test\Unit\GraphQL\Annotated\MyDirective;
use Siler\Test\Unit\GraphQL\Annotated\Query;
use TypeError;
use function Siler\GraphQL\{annotated, execute};

class AnnotatedTest extends TestCase
{
    public function testAnnotated()
    {
        $schema = annotated([
            MyDirective::class,
            Enum::class,
            IFoo::class, Foo::class, Bar::class, FooBar::class,
            Input::class, Query::class, Mutation::class,
        ]);
        $schema->assertValid();

        $this->assertTrue($schema->hasType('Query'));
        $this->assertTrue($schema->hasType('Mutation'));

        $result = execute($schema, ['query' => 'query { hello }']);
        $this->assertSame(['data' => ['hello' => 'world']], $result);

        $result = execute($schema, ['query' => 'mutation { sum(input: {x: 2, y: 2}) }']);
        $this->assertSame(['data' => ['sum' => 4]], $result);

        $result = execute($schema, ['query' => 'query { foo { enum foo } }']);
        $this->assertSame(['data' => ['foo' => ['enum' => Enum::YES, 'foo' => 'foo']]], $result);

        $result = execute($schema, ['query' => 'query { bar { myBool myFloat } }']);
        $this->assertSame(['data' => ['bar' => ['myBool' => true, 'myFloat' => 4.2]]], $result);

        $this->assertNotNull($schema->getDirective('myDirective'));

        $result = execute($schema, ['query' => 'query { baz }']);
        $this->assertSame(['data' => ['baz' => 'Baz']], $result);
    }

    public function testListOfException()
    {
        $this->expectException(TypeError::class);
        annotated(ListOfException::class);
    }
}
