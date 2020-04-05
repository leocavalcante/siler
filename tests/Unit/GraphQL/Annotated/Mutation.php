<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Args;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType() */
class Mutation
{
    /**
     * @Field()
     * @Args({@Field(name="input", type=Input::class)})
     */
    public static function sum($root, $args): int
    {
        $input = Input::fromArray($args['input']);
        return $input->x + $input->y;
    }
}
