<?php declare(strict_types=1);

namespace Siler\Test\Unit\Stratigility;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Siler\Diactoros;
use Siler\Stratigility\RequestHandlerDecorator;

/**
 * @package Siler\Test\Unit\Stratigility
 */
class RequestHandlerDecoratorTest extends TestCase
{
    public function testHandle()
    {
        $request = new ServerRequest();
        $response = Diactoros\response();
        $params = ['foo' => 'bar'];

        $handler = function (
            ServerRequestInterface $_request,
            array $_pathParams
        ) use (
            $request,
            $response,
            $params
        ): ResponseInterface {
            $this->assertSame($request, $_request);
            $this->assertSame($params, $_pathParams);

            return $response;
        };

        $decorator = new RequestHandlerDecorator($handler, $params);
        $decorator_response = $decorator->handle($request);

        $this->assertSame($response, $decorator_response);
    }
}
