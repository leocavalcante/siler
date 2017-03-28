<?php

namespace Siler\Test\Unit;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IdType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use Siler\Graphql;

class GraphqlTypeDeclarationTest extends TestCase
{
    public function testStr()
    {
        $this->assertInstanceOf(StringType::class, Graphql\str());
    }

    public function testInt()
    {
        $this->assertInstanceOf(IntType::class, Graphql\int());
    }

    public function testFloat()
    {
        $this->assertInstanceOf(FloatType::class, Graphql\float());
    }

    public function testBool()
    {
        $this->assertInstanceOf(BooleanType::class, Graphql\bool());
    }

    public function testId()
    {
        $this->assertInstanceOf(IdType::class, Graphql\id());
    }

    public function testListOf()
    {
        $this->assertInstanceOf(ListOfType::class, Graphql\list_of(Graphql\int()));
    }
}
