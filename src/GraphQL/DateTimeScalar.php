<?php declare(strict_types=1);

namespace Siler\GraphQL;

class DateTimeScalar extends DateScalar
{
    /** @var string */
    public const FORMAT = 'Y-m-d H:i:s';

    /** @var string */
    public $name = 'DateTime';
}
