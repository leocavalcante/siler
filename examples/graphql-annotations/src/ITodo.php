<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\InterfaceType;

/** @InterfaceType */
interface ITodo
{
    /**
     * @Field(type=TodoStatus::class)
     * @param Todo $todo
     * @return int
     */
    public static function status(Todo $todo): int;
}
