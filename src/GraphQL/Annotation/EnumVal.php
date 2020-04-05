<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

/**
 * @Annotation
 * @psalm-suppress MissingConstructor
 */
class EnumVal
{
    /** @var string|null */
    public $name;
    /** @var string|null */
    public $description;
    /** @var mixed */
    public $value;
}
