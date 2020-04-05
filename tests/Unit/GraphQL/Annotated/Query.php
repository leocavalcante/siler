<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Query
{
    /** @Field */
    public static function hello(): string
    {
        return 'world';
    }
}
