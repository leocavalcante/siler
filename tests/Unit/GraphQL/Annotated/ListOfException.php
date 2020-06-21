<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/**
 * @ObjectType()
 */
final class ListOfException
{
    /**
     * @Field()
     */
    public function listOf(): array
    {
    }
}
