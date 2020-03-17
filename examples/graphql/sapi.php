<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

use GraphQL\Error\UserError;
use Monolog\Handler\ErrorLogHandler;
use Siler\Monolog as Log;
use function Siler\GraphQL\{debug, directives, init, listen, schema, subscriptions_at, subscriptions_manager};
use const Siler\GraphQL\{ON_CONNECT, ON_OPERATION};

require_once __DIR__ . '/../../vendor/autoload.php';

Log\handler(new ErrorLogHandler());

debug();
subscriptions_at('ws://localhost:8001');

listen(ON_CONNECT, function (array $connParams): array {
    if (empty($connParams['authToken'])) {
        throw new UserError('Unauthenticated', 401);
    }

    if ($connParams['authToken'] !== 'SilerIsTheBest') {
        throw new UserError('Unauthorized', 403);
    }

    return ['user' => ['roles' => ['inbox']]];
});

listen(ON_OPERATION, function (array $subscription, $_, $context) {
    if ($subscription['name'] === 'inbox') {
        if (empty($context['user'])) {
            throw new UserError('Unauthenticated', 401);
        }

        if (!in_array('inbox', $context['user']['roles'])) {
            throw new UserError('Unauthorized', 403);
        }
    }
});

$resolvers = require_once __DIR__ . '/resolvers.php';
$directives = require_once __DIR__ . '/directives.php';
$root_value = [];
$context = [];

$type_defs = file_get_contents(__DIR__ . '/schema.graphql');
$schema = schema($type_defs, $resolvers);
directives($directives);

init($schema, $root_value, $context);
