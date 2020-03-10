<?php declare(strict_types=1);

namespace Siler\Klass;

use function Siler\Functional\last;

/**
 * @param string $className
 * @psalm-param class-string $className
 * @return mixed|null
 */
function unqualified_name(string $className)
{
    return last(explode('\\', $className));
}
