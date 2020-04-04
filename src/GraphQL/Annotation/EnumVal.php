<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

/**
 * @Annotation
 */
class EnumVal
{
    /** @var string */
    public $name;
    /** @var string */
    public $description;
    /** @var mixed */
    public $value;
}
