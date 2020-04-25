<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType() */
class Query
{
    /** @Field() */
    public static function hello(): string
    {
        return 'world';
    }

    /** @Field() */
    public static function foo(): Foo
    {
        return new Foo();
    }

    /** @Field(type=Bar::class) */
    public static function bar(): Bar
    {
        return new Bar();
    }

    public static function noop()
    {
    }
}
