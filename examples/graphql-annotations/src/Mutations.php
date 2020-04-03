<?php declare(strict_types=1);

namespace App;

use GraphQL\Type\Definition\Type;
use Siler\GraphQL\Annotation\Args;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;
use function Siler\array_get_int;

/** @ObjectType(name="Mutation") */
class Mutations
{
    /**
     * @Field(description="Sums two integers")
     * @Args({"x" = Type::INT, "y" = Type::INT})
     */
    public static function sum($_, array $args): int
    {
        $x = array_get_int($args, 'x');
        $y = array_get_int($args, 'y');

        return $x + $y;
    }
}
