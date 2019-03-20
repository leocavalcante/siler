<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Stratigility;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Diactoros;
use Siler\Stratigility;
use Zend\Diactoros\ServerRequest;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityTest extends TestCase
{
    public function testProcessThrowsWhenNull()
    {
        $this->expectException(\UnexpectedValueException::class);
        Stratigility\process(new ServerRequest(), 'null_process_test');
    }

    public function testProcessThrowsWhenNotMiddlewarePipe()
    {
        $this->expectException(\UnexpectedValueException::class);

        Container\set('not_middlewarepipe', 1);
        Stratigility\process(new ServerRequest(), 'not_middlewarepipe');
    }

    public function testProcess()
    {
        $middleware = function ($request, $handler) {
            $step = $request->getAttribute('step');
            $this->assertEquals(1, $step);

            return $handler->handle($request->withAttribute('step', $step + 1));
        };

        $handler = function ($request, $params) {
            $step = $request->getAttribute('step');
            $this->assertEquals(2, $step);
            $this->assertEquals('bar', $params['foo']);

            return Diactoros\response();
        };

        Stratigility\pipe($middleware);
        Stratigility\process((new ServerRequest())->withAttribute('step', 1))($handler)(['foo' => 'bar']);
    }

    public function testPipe()
    {
        $middleware = function ($request, $handler) {
            return $handler->handle($request);
        };

        $pipeline = Stratigility\pipe($middleware, 'pipe_test');

        $this->assertInstanceOf(MiddlewarePipe::class, Container\get('pipe_test'));
        $this->assertInstanceOf(MiddlewarePipe::class, $pipeline);
        $this->assertSame($pipeline, Container\get('pipe_test'));
    }

    public function testHandleThrowsWhenNull()
    {
        $this->expectException(\UnexpectedValueException::class);
        Stratigility\handle(new ServerRequest(), 'null_handle_test');
    }

    public function testHandleThrowsWhenNotMiddlewarePipe()
    {
        $this->expectException(\UnexpectedValueException::class);

        Container\set('not_middlewarepipe', 1);
        Stratigility\handle(new ServerRequest(), 'not_middlewarepipe');
    }

    public function testHandle()
    {
        $payload = ['handle' => 'test'];

        $middleware = function ($request, $handler) use ($payload) {
            return Diactoros\json($payload);
        };

        Stratigility\pipe($middleware, 'handle_test');

        $response = Stratigility\handle(new ServerRequest(), 'handle_test');

        $this->assertSame($payload, $response->getPayload());
    }
}
