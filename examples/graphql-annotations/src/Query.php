<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use DateTime;
use GraphQL\Type\Definition\ResolveInfo;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;
use function Siler\Functional\always;
use function Siler\Functional\map;

/** @ObjectType */
class Query
{
    /** @Field(description="A common greet") */
    public static function hello($root, array $args, $context, ResolveInfo $info): string
    {
        return 'world';
    }

    /** @Field */
    public static function answer(): int
    {
        return 42;
    }

    /** @Field */
    public static function now(): DateTime
    {
        return new DateTime();
    }

    /** @Field */
    public static function fooBar(): FooBar
    {
        return rand(0, 100) > 50 ? new Foo() : new Bar();
    }

    /**
     * @Field(listOf=Todo::class)
     * @return Todo[]
     */
    public static function todos(): array
    {
        $parent = new Todo('Parent todo');
        $todo = new Todo('Something to do');
        $todo->parent = $parent;
        return [$todo];
    }

    public static function dynamicFields(): array
    {
        return map(range(1, 10), fn(int $i) => (new Field())->name("index$i")->type('Int')->resolve(always($i)));
    }
}
