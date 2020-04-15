<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType(name="Query") */
class HelloWorld
{
    /**
     * @Field(description="A common greet")
     */
    public static function hello(): string
    {
        return 'Hello, World!';
    }
}
