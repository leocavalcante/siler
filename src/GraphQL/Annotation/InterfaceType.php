<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 * @psalm-suppress MissingConstructor
 */
class InterfaceType
{
    /**
     * @var string
     * @psalm-var string|null
     */
    public $name;
    /**
     * @var string
     * @psalm-var string|null
     */
    public $description;
}
