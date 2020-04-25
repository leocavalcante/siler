<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

use GraphQL\Error\UserError;
use Monolog\Handler\ErrorLogHandler;
use Siler\Monolog as Log;
use Swoole\Runtime;
use function Siler\GraphQL\{debug, directives, listen, schema, subscriptions_at, subscriptions_manager};
use function Siler\Swoole\{graphql_handler, graphql_subscriptions, http_server_port};
use const Siler\GraphQL\{ON_CONNECT, ON_OPERATION};

require_once __DIR__ . '/../../vendor/autoload.php';

Runtime::enableCoroutine();
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
$filters = require_once __DIR__ . '/filters.php';
$root_value = [];
$context = [];

$type_defs = file_get_contents(__DIR__ . '/schema.graphql');
$schema = schema($type_defs, $resolvers);
directives($directives);

$manager = subscriptions_manager($schema, $filters, $root_value, $context);

$server = graphql_subscriptions($manager, 8001);
$server->set(['upload_tmp_dir' => __DIR__ . '/uploads']);

http_server_port($server, graphql_handler($schema, $root_value, $context), 8000);
$server->start();
