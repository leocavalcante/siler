<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use GraphQL\Type\Definition\Type;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Todo implements ITodo
{
    /**
     * @Field(type = Type::ID)
     * @var string
     */
    public $id;

    /**
     * @Field(type = Type::STRING)
     * @var string
     */
    public $title;

    /** @var int */
    private $status;

    /**
     * @Field(type = Todo::class, nullable = true, description="Just to test recursive types")
     * @var Todo|null
     */
    public $parent;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->status = TodoStatus::TODO;
    }

    /**
     * @Field(type = TodoStatus::class)
     * @param Todo $todo
     * @return int
     */
    public static function status(Todo $todo): int
    {
        return $todo->status;
    }
}
