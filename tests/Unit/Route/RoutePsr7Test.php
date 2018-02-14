<?php

namespace Siler\Test\Unit;

use Siler\Container;
use Siler\Route;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class RoutePsr7Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRoute()
    {
        $server = ['REQUEST_URI' => '/test'];
        $request = ServerRequestFactory::fromGlobals($server);

        $actual = Route\get('/test', function () {
            return 'foo';
        }, $request);

        $this->assertSame('foo', $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNullRoute()
    {
        $server = ['REQUEST_URI' => '/foo'];
        $request = ServerRequestFactory::fromGlobals($server);

        $actual = Route\get('/bar', function () {
            return 'baz';
        }, $request);

        $this->assertNull($actual);
    }
}
