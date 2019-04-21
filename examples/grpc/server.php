<?php declare(strict_types=1);

$base_path = realpath(__DIR__ . '/../..');
require_once $base_path . '/vendor/autoload.php';
require_once $base_path . '/examples/grpc/vendor/autoload.php';

use Helloworld\Greeter;
use Siler\Grpc;

$services = ['helloworld.Greeter' => new Greeter()];

echo "Listening at localhost:9090\n";
Grpc\server($services)->start();
