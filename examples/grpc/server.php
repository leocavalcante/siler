<?php declare(strict_types=1);

use Helloworld\Greeter;
use Siler\Grpc;
use Swoole\Runtime;

$base_path = realpath(__DIR__ . '/../..');

require_once $base_path . '/vendor/autoload.php';
require_once $base_path . '/examples/grpc/vendor/autoload.php';

Runtime::enableCoroutine();

$services = ['helloworld.Greeter' => new Greeter()];
$server = Grpc\server($services);

echo "Listening at localhost:9090\n";
$server->start();
