<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Field
{
    /** @var string */
    public $type;
    /** @var string */
    public $name;
    /** @var string */
    public $description;
    /** @var string */
    public $listOf;
}
