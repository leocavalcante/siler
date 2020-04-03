<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class InputType
{
    /** @var string */
    public $name;
}
