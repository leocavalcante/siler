<?php declare(strict_types=1);


namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\UnionType;

/** @UnionType(types={Foo::class, Bar::class}) */
abstract class FooBar
{
    /**
     * @Field(type="Float")
     * @var float
     */
    public $baz = 4.2;
}
