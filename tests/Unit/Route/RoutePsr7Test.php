<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Siler\Route;

class RoutePsr7Test extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testRoute()
    {
        $server = ['REQUEST_URI' => '/test'];
        $request = ServerRequestFactory::fromGlobals($server);

        $actual = Route\get(
            '/test',
            function () {
                return 'foo';
            },
            $request
        );

        $this->assertSame('foo', $actual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testNullRoute()
    {
        $server = ['REQUEST_URI' => '/foo'];
        $request = ServerRequestFactory::fromGlobals($server);

        $actual = Route\get(
            '/bar',
            function () {
                return 'baz';
            },
            $request
        );

        $this->assertNull($actual);
    }
}
