<?php declare(strict_types=1);

use Grpc\ChannelCredentials;
use Helloworld\GreeterClient;
use Helloworld\HelloReply;
use Helloworld\HelloRequest;
use Swoole\Runtime;

$base_path = realpath(__DIR__ . '/../..');

require_once $base_path . '/vendor/autoload.php';
require_once $base_path . '/examples/grpc/vendor/autoload.php';

Runtime::enableCoroutine();

go(function () {
    $greeter = new GreeterClient('localhost:9090', [
        'credentials' => ChannelCredentials::createInsecure(),
    ]);

    $request = new HelloRequest();
    $request->setName('Siler');

    /** @var HelloReply|null $reply */
    list($reply, $status) = $greeter->sayHello($request)->wait();

    if ($reply === null) {
        echo $status->details, PHP_EOL;
        return;
    }

    echo $reply->getMessage(), PHP_EOL;
});
