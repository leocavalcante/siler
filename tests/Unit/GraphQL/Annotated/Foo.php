<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType() */
class Foo extends FooBar implements IFoo
{
    /**
     * @Field(type="Boolean")
     * @var bool
     */
    public $enum = Enum::YES;

    /** @Field() */
    public function foo(): string
    {
        return 'foo';
    }
}
