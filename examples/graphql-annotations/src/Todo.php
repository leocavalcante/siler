<?php declare(strict_types=1);

namespace App;

use GraphQL\Type\Definition\Type;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Todo
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

    /**
     * @Field(type = TodoStatus::class)
     * @var TodoStatus
     */
    public $status;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->done = false;
        $this->status = TodoStatus::TODO;
    }
}
