<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Directive
{
    /** @var string */
    public $name;
    /** @var string */
    public $description;
    /** @var array<string> */
    public $locations;
}
