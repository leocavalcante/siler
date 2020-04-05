<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType() */
class Bar extends FooBar
{
    /** @Field() */
    public static function myBool(): bool
    {
        return true;
    }

    /** @Field() */
    public static function myFloat(): float
    {
        return 4.2;
    }
}
