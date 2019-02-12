<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IdType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use Siler\GraphQL;

class GraphQLTypeDeclarationTest extends TestCase
{


    public function testStr()
    {
        $this->assertInstanceOf(StringType::class, GraphQL\str());
    }


    public function testInt()
    {
        $this->assertInstanceOf(IntType::class, GraphQL\int());
    }


    public function testFloat()
    {
        $this->assertInstanceOf(FloatType::class, GraphQL\float());
    }


    public function testBool()
    {
        $this->assertInstanceOf(BooleanType::class, GraphQL\bool());
    }


    public function testId()
    {
        $this->assertInstanceOf(IdType::class, GraphQL\id());
    }


    public function testListOf()
    {
        $this->assertInstanceOf(ListOfType::class, GraphQL\list_of(GraphQL\int()));
    }
}
