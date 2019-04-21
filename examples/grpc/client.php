<?php declare(strict_types=1);

use Helloworld\GreeterClient;
use Helloworld\HelloReply;
use Helloworld\HelloRequest;
use Swoole\Runtime;

$base_path = realpath(__DIR__ . '/../..');

require_once $base_path . '/vendor/autoload.php';
require_once $base_path . '/examples/grpc/vendor/autoload.php';

Runtime::enableCoroutine();

go(function () {
    $greeter = new GreeterClient('localhost:9090', []);
    $greeter->start();

    $request = new HelloRequest();
    $request->setName('Siler');

    /** @var $reply string|HelloReply */
    /** @var $status int */
    list($reply, $status) = $greeter->sayHello($request);
    $greeter->close();

    echo "Status: $status\n";

    if (is_string($reply)) {
        echo "$reply\n";
        exit(1);
    } else {
        echo $reply->getMessage() . "\n";
    }
});
