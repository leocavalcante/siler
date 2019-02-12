<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\GraphQL;

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$host   = '0.0.0.0';
$port   = 5000;
$schema = include __DIR__ . '/schema.php';

GraphQL\listen(
    GraphQL\ON_CONNECT,
    function (array $connParams) {
        if (empty($connParams['authToken'])) {
            throw new Exception('Unauthenticated');
        }

        $findUserSomehow = function (string $authToken): ?array {
            if ($authToken === 'siler<3graphql') {
                return [
                    'id'    => 1,
                    'name'  => 'Siler',
                    'roles' => ['inbox'],
                ];
            }

            return null;
        };

        if (empty($user = $findUserSomehow($connParams['authToken']))) {
            throw  new Exception('Unauthorized');
        }

        // Will be merged into the context.
        return ['user' => $user];
    }
);

GraphQL\listen(
    GraphQL\ON_OPERATION,
    function (array $subscription, array $rootValue, array $context) {
        if ($subscription['name'] === 'inbox' && (empty($context['user']) || !in_array('inbox', $context['user']['roles']))) {
            throw new Exception('Forbidden');
        }
    }
);

printf("Listening at %s:%s\n", $host, $port);
GraphQL\subscriptions($schema, $filters, $host, $port)->run();
