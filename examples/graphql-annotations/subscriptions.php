<?php declare(strict_types=1);

namespace Siler\Example\GraphQL\Annotation;

use Siler\GraphQL;
use Siler\Swoole;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

$schema = require __DIR__ . '/schema.php';
$manager = GraphQL\subscriptions_manager($schema);
$server = Swoole\graphql_subscriptions($manager);

echo "Subscriptions listening on port 3000\n";
$server->start();
