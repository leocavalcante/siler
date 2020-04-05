<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL\Annotated;

use Siler\GraphQL\Annotation\{EnumType, EnumVal};

/** @EnumType() */
class Enum
{
    /**
     * @EnumVal()
     * @var bool
     */
    public const YES = true;
    /**
     * @EnumVal()
     * @var bool
     */
    public const NO = false;
}
