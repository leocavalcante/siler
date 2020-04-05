<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\InterfaceType;

/** @InterfaceType() */
interface IFoo
{
    /** @Field() */
    public function foo(): string;
}
