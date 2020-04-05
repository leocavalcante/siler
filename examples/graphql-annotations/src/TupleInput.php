<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\{Field, InputType};
use Siler\Prelude\FromArray;

/** @InputType */
class TupleInput
{
    use FromArray;

    /**
     * @Field(type = "Int")
     * @var int
     */
    public $x;
    /**
     * @Field(type = "Int")
     * @var int
     */
    public $y;
}
