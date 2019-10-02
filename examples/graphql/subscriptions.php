<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\GraphQL;

GraphQL\debug();

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    }
];

$schema = include __DIR__ . '/schema.php';

GraphQL\listen(GraphQL\ON_CONNECT, function (array $connParams) {
    if (empty($connParams['authToken'])) {
        throw new Exception('Unauthenticated');
    }

    $findUserSomehow = function (string $authToken): ?array {
        if ($authToken === 'siler<3graphql') {
            return [
                'id' => 1,
                'name' => 'Siler',
                'roles' => ['inbox']
            ];
        }

        return null;
    };

    if (empty(($user = $findUserSomehow($connParams['authToken'])))) {
        throw new Exception('Unauthorized');
    }

    // Will be merged into the context.
    return ['user' => $user];
});

GraphQL\listen(GraphQL\ON_OPERATION, function (array $subscription, array $rootValue, array $context) {
    print_r($context);

    if (
        $subscription['name'] === 'inbox' &&
        (empty($context['user']) || !in_array('inbox', $context['user']['roles']))
    ) {
        throw new Exception('Forbidden');
    }
});

$manager = GraphQL\subscriptions_manager($schema, $filters);

$port = 3000;
printf("Listening at %s\n", $port);
Siler\Ratchet\graphql_subscriptions($manager, $port)->run();
