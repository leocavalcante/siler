<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType() */
class Bar extends FooBar
{
    /**
     * @Field(type="Boolean")
     * @var bool
     */
    public $bool = true;
    /**
     * @Field(type="Float")
     * @var float
     */
    public $float = 4.2;
}
