<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class EnumType
{
    /** @var string */
    public $name;
    /** @var string */
    public $description;
}
