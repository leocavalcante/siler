<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\Args;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;
use function Siler\array_get_arr;

/** @ObjectType */
class Mutation
{
    /**
     * @Field(description="Sums two integers")
     * @Args({
     *     @Field(name="input", type=TupleInput::class)
     * })
     */
    public static function sum($_, array $args): int
    {
        $input = TupleInput::fromArray(array_get_arr($args, 'input'));
        return $input->x + $input->y;
    }
}
