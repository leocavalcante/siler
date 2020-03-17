<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

use GraphQL\Error\UserError;
use Monolog\Handler\ErrorLogHandler;
use Siler\Monolog as Log;
use Swoole\Runtime;
use function Siler\GraphQL\{debug, listen, schema, subscriptions_at, subscriptions_manager, with_upload};
use function Siler\Swoole\{graphql_handler, graphql_subscriptions, http_server_port};
use const Siler\GraphQL\{ON_CONNECT, ON_OPERATION};

$dir = __DIR__;
$base_dir = dirname($dir, 2);

require_once "$base_dir/vendor/autoload.php";

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

listen(ON_OPERATION, function (array $subscription, $rootValue, array $context) {
    if ($subscription['name'] === 'inbox') {
        if (empty($context['user'])) {
            throw new UserError('Unauthenticated', 401);
        }

        if (!in_array('inbox', $context['user']['roles'])) {
            throw new UserError('Unauthorized', 403);
        }
    }
});

$type_defs = file_get_contents("$dir/schema.graphql");
$resolvers = require_once "$dir/resolvers.php";
$schema = schema($type_defs, $resolvers);

$filters = [
    'inbox' => function (array $payload, array $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$root_value = [];
$context = [];
$manager = subscriptions_manager($schema, $filters, $root_value, $context);

$server = graphql_subscriptions($manager, 8001);
$server->set(['upload_tmp_dir' => __DIR__ . '/uploads']);

http_server_port($server, graphql_handler($schema, $root_value, $context), 8000);
$server->start();

