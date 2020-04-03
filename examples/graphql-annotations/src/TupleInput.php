<?php declare(strict_types=1);

namespace App;

use GraphQL\Type\Definition\Type;
use Siler\GraphQL\Annotation\{Field, InputType};
use Siler\Prelude\FromArray;

/** @InputType */
class TupleInput
{
    use FromArray;

    /**
     * @Field(type = Type::INT)
     * @var int
     */
    public $x;
    /**
     * @Field(type = Type::INT)
     * @var int
     */
    public $y;
}
