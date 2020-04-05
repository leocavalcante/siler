<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY", "ANNOTATION"})
 * @psalm-suppress MissingConstructor
 */
final class Field
{
    /** @var string|null */
    public $type;
    /** @var string|null */
    public $name;
    /** @var string|null */
    public $description;
    /** @var string|null */
    public $listOf;
}
