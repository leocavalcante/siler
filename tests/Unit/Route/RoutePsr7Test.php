<?php

namespace Siler\Test\Unit;

use Siler\Container;
use Siler\Route;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class RoutePsr7Test extends \PHPUnit\Framework\TestCase
{
    public function testPsr7()
    {
        $request = new ServerRequest();
        Route\psr7($request);

        $this->assertSame($request, Container\get('psr7_request'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRoute()
    {
        $server = ['REQUEST_URI' => '/test'];
        $request = ServerRequestFactory::fromGlobals($server);

        Route\psr7($request);

        $actual = Route\get('/test', function () {
            return 'foo';
        });

        $this->assertEquals('foo', $actual);
    }
}
