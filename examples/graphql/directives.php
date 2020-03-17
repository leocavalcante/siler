<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

use Closure;

return [
    'upper' => function (callable $resolver): Closure {
        return function ($root, $args, $context, $info) use ($resolver): string {
            $value = $resolver($root, $args, $context, $info);
            return mb_strtoupper($value, 'UTF-8');
        };
    },
];
