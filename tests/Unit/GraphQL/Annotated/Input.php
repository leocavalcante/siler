<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\InputType;
use Siler\Prelude\FromArray;

/** @InputType() */
class Input
{
    use FromArray;

    /**
     * @Field(type="Int")
     * @var int
     */
    public $x;
    /**
     * @Field(type="Int")
     * @var int
     */
    public $y;
}
