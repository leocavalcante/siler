<?php declare(strict_types=1);

namespace Helloworld;

use Siler\Grpc;
use Swoole\Runtime;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/clients/php/vendor/autoload.php';

Runtime::enableCoroutine();

class Greeter
{
    public function sayHello(HelloRequest $request): HelloReply
    {
        $reply = new HelloReply();
        $reply->setMessage('Hello ' . $request->getName());
        return $reply;
    }
}

$services = ['helloworld.Greeter' => new Greeter()];
$server = Grpc\server($services);

echo "Listening at localhost:9090\n";
$server->start();
