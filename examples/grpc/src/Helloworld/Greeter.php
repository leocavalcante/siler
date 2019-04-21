<?php declare(strict_types=1);

namespace Helloworld;

class Greeter
{
    public function sayHello(HelloRequest $request): HelloReply
    {
        $reply = new HelloReply();
        $reply->setMessage('Hello ' . $request->getName());
        return $reply;
    }
}
