<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use Siler\GraphQL\Annotation as GQL;

/**
 * @GQL\ObjectType()
 */
final class Subscription
{
    /**
     * @GQL\Field()
     */
    public static function ekko(string $message): string
    {
        return $message;
    }
}
