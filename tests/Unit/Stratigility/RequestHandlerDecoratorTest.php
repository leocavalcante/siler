<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Stratigility;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Siler\Diactoros;
use Siler\Stratigility\RequesthandlerDecorator;
use Zend\Diactoros\ServerRequest;

class RequestHandlerDecoratorTest extends TestCase
{


    public function testHandle()
    {
        $request    = new ServerRequest();
        $response   = Diactoros\response();
        $pathParams = ['foo' => 'bar'];

        $handler = function (ServerRequestInterface $_request, array $_pathParams) use ($request, $response, $pathParams): ResponseInterface {
            $this->assertSame($request, $_request);
            $this->assertSame($pathParams, $_pathParams);

            return $response;
        };

        $decorator = new RequestHandlerDecorator($handler, $pathParams);
        $_response = $decorator->handle($request);

        $this->assertSame($response, $_response);
    }
}
