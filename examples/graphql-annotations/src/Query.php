<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use GraphQL\Type\Definition\ResolveInfo;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Query
{
    /** @Field(description="A common greet") */
    public static function hello($root, array $args, $context, ResolveInfo $info): string
    {
        return 'world';
    }

    /** @Field */
    public static function answer(): int
    {
        return 42;
    }

    /** @Field */
    public static function now(): \DateTime
    {
        return new \DateTime();
    }

    /** @Field */
    public static function fooBar(): FooBar
    {
        return rand(0, 100) > 50 ? new Foo() : new Bar();
    }

    /**
     * @Field(listOf=Todo::class)
     * @return Todo[]
     */
    public static function todos(): array
    {
        return [new Todo('Something to do')];
    }
}
