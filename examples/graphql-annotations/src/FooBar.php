<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\UnionType;

/** @UnionType(types={Foo::class, Bar::class}) */
abstract class FooBar
{
    /**
     * @Field(type="String")
     * @var string
     */
    public $baz = 'foobar';
}
