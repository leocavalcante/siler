<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

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

    /**
     * @Field(description="Sums of tuples", listOf="int")
     * @Args({
     *     @Field(name="inputs", listOf=TupleInput::class, nullableList=true)
     * })
     * @return array
     */
    public static function sums($_, array $args): array
    {
        return array_map(static function (array $input) {
            $input = TupleInput::fromArray($input);
            return $input->x + $input->y;
        }, array_get_arr($args, 'inputs'));
    }
}
