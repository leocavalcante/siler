<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType(name="Query") */
class RootQuery
{
    /** @Field(description="A common greet") */
    public static function hello(): string
    {
        return 'world';
    }

    /** @Field */
    public static function answer(): int
    {
        return 42;
    }
}
