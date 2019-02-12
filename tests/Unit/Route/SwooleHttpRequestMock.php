<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Route;

class SwooleHttpRequestMock
{
    public $server;


    public function __construct(string $method, string $uri)
    {
        $this->server = [
            'request_method' => $method,
            'request_uri'    => $uri,
        ];
    }
}
