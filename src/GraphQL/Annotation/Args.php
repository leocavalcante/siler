<?php declare(strict_types=1);

namespace Siler\GraphQL\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Args
{
    /** @var Field[] */
    public $fields;

    public function __construct(array $values)
    {
        $this->fields = $values['value'];
    }
}
