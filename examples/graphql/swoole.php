<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

use GraphQL\Error\UserError;
use Monolog\Handler\ErrorLogHandler;
use Siler\GraphQL;
use Siler\Http\Response;
use Siler\Monolog as Log;
use Siler\Route;
use Siler\Swoole;
use Swoole\Runtime;

require_once __DIR__ . '/../../vendor/autoload.php';

Runtime::enableCoroutine();
Log\handler(new ErrorLogHandler());

GraphQL\debug();
GraphQL\subscriptions_at('ws://localhost:8001');

GraphQL\listen(GraphQL\ON_CONNECT, function (array $connParams): array {
    if (empty($connParams['authToken'])) {
        throw new UserError('Unauthenticated', 401);
    }

    if ($connParams['authToken'] !== 'SilerIsTheBest') {
        throw new UserError('Unauthorized', 403);
    }

    return ['user' => ['roles' => ['inbox']]];
});

GraphQL\listen(GraphQL\ON_OPERATION, function (array $subscription, $_, $context) {
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
$schema = GraphQL\schema($type_defs, $resolvers);
GraphQL\directives($directives);

$manager = GraphQL\subscriptions_manager($schema, $filters, $root_value, $context);

$server = Swoole\graphql_subscriptions($manager, 8001);
$server->set(['upload_tmp_dir' => __DIR__ . '/uploads']);

Swoole\http_server_port($server, function () use ($schema, $root_value, $context) {
    Route\post('/graphql', fn() => Response\json(GraphQL\execute($schema, GraphQL\request()->toArray(), $root_value, $context)));
    Route\get('/graphiql', fn() => Response\html(GraphQL\graphiql()));
}, 8000);

$server->start();
