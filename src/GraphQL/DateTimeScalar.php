<?php declare(strict_types=1);

namespace Siler\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DateTimeScalar extends DateScalar
{
    /** @var string */
    public const FORMAT = 'Y-m-d H:i:s';

    /** @var string */
    public $name = 'DateTime';
}
