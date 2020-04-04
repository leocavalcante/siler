<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Query
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

    /** @Field */
    public static function now(): \DateTime
    {
        return new \DateTime();
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
