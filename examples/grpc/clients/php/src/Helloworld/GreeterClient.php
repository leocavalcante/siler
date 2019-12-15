<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Helloworld;

use Grpc\BaseStub;
use Grpc\Channel;

/**
 */
class GreeterClient extends BaseStub
{

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null)
    {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param HelloRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function sayHello(HelloRequest $argument,
                             $metadata = [], $options = [])
    {
        return $this->_simpleRequest('/helloworld.Greeter/sayHello',
            $argument,
            ['\Helloworld\HelloReply', 'decode'],
            $metadata, $options);
    }

}
