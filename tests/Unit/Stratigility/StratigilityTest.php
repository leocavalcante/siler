<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Stratigility;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Diactoros;
use Siler\Stratigility;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityTest extends TestCase
{
    /**
     * @expectedException UnexpectedValueException
     */
    public function testProcessThrowsWhenNull()
    {
        Stratigility\process(Diactoros\request(), 'null_process_test');
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testProcessThrowsWhenNotMiddlewarePipe()
    {
        Container\set('not_middlewarepipe', 1);
        Stratigility\process(Diactoros\request(), 'not_middlewarepipe');
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
        Stratigility\process(Diactoros\request()->withAttribute('step', 1))($handler)(['foo' => 'bar']);
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

    /**
     * @expectedException UnexpectedValueException
     */
    public function testHandleThrowsWhenNull()
    {
        Stratigility\handle(Diactoros\request(), 'null_handle_test');
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testHandleThrowsWhenNotMiddlewarePipe()
    {
        Container\set('not_middlewarepipe', 1);
        Stratigility\handle(Diactoros\request(), 'not_middlewarepipe');
    }

    public function testHandle()
    {
        $payload = ['handle' => 'test'];

        $middleware = function ($request, $handler) use ($payload) {
            return Diactoros\json($payload);
        };

        Stratigility\pipe($middleware, 'handle_test');

        $response = Stratigility\handle(Diactoros\request(), 'handle_test');

        $this->assertSame($payload, $response->getPayload());
    }
}
