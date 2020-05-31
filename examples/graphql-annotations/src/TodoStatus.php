<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use Siler\GraphQL\Annotation\EnumType;
use Siler\GraphQL\Annotation\EnumVal;

/** @EnumType */
class TodoStatus
{
    /**
     * @EnumVal(description="Not done yet")
     * @var int
     */
    public const TODO = 1;
    /**
     * @EnumVal
     * @var int
     */
    public const DONE = 2;

    public const SHOULD_BE_IGNORED = -1;
}
