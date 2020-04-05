<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 * @psalm-suppress MissingConstructor
 */
class UnionType
{
    /** @var string|null */
    public $name;
    /** @var string|null */
    public $description;
    /** @var string[]|null */
    public $types;
}
