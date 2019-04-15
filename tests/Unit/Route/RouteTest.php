<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Route;
use Siler\Test\Unit\Route\SwooleHttpRequestMock;
use Zend\Diactoros\ServerRequest;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

class RouteTest extends TestCase
{
    /**
     * Test route with request parameter as an array.
     */
    public function testRouteWithRequest()
    {
        $this->expectOutputString('bar');

        Route\route(
            'get',
            '/foo',
            function () {
                echo 'bar';
            },
            ['get', '/foo']
        );
    }

    public function testRouteMatching()
    {
        $this->expectOutputString('baz');

        Route\route('get', '/foo', function ($params) {
            echo 'foo';
        });

        Route\route('get', '/bar', function ($params) {
            echo 'bar';
        });

        Route\route('get', '/bar/baz', function ($params) {
            echo 'baz';
        });
    }

    public function testRouteRegexp()
    {
        $this->expectOutputString('baz');

        Route\route('get', '/bar/([a-z]+)', function ($params) {
            echo $params[1];
        });
    }

    public function testRouteNamedGroup()
    {
        $this->expectOutputString('baz');

        Route\route('get', '/bar/{baz}', function ($params) {
            echo $params['baz'];
        });
    }

    public function testOptionalParam()
    {
        $this->expectOutputString('qux');

        Route\route('get', '/bar/baz/?{qux}?', function ($params) {
            echo array_key_exists('qux', $params) ? 'foobar' : 'qux';
        });
    }

    public function testOptionalParamMatch()
    {
        $this->expectOutputString('biz');

        $_SERVER['REQUEST_URI'] = '/bar/baz/biz';

        Route\route('get', '/bar/baz/?{qux}?', function ($params) {
            echo array_key_exists('qux', $params) ? $params['qux'] : 'qux';
        });
    }

    public function testRouteWrappedNamedGroup()
    {
        $this->expectOutputString('baz');

        $_SERVER['REQUEST_URI'] = '/bar/baz/qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            echo 'foo';
        });

        Route\route('get', '/bar/{baz}/qux', function ($params) {
            echo $params['baz'];
        });
    }

    public function testRouteNamedGroupWithDash()
    {
        $this->expectOutputString('baz-qux');

        $_SERVER['REQUEST_URI'] = '/bar/baz-qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            echo 'baz-qux';
        });
    }

    public function testRouteNamedGroupWithNumber()
    {
        $this->expectOutputString('baz-2017');

        $_SERVER['REQUEST_URI'] = '/bar/baz-2017';

        Route\route('get', '/bar/{baz}', function ($params) {
            echo $params['baz'];
        });
    }

    public function testRouteNamedGroupWithUnderscore()
    {
        $this->expectOutputString('baz_qux');

        $_SERVER['REQUEST_URI'] = '/bar/baz_qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            echo $params['baz'];
        });
    }

    public function testRouteDefaultPathInfo()
    {
        $this->expectOutputString('foo');

        unset($_SERVER['REQUEST_URI']);

        Route\route('get', '/', function ($params) {
            echo 'foo';
        });
    }

    public function testRouteWithString()
    {
        $this->expectOutputString('foo');
        Route\route('get', '/bar/{bar}', __DIR__ . '/../../fixtures/to_be_required.php');
    }

    public function testRouteMethod()
    {
        $this->expectOutputString('bar');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\route('get', '/bar/baz', function ($params) {
            echo 'foo';
        });

        Route\route('post', '/bar/baz', function ($params) {
            echo 'bar';
        });
    }

    public function testRouteMultiMethods()
    {
        $this->expectOutputString('foobar');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\route(['get', 'post'], '/bar/baz', function ($params) {
            echo 'foo';
        });

        Route\route('post', '/bar/baz', function ($params) {
            echo 'bar';
        });
    }

    public function testRouteReturn()
    {
        $actual = Route\route('get', '/bar/baz', function () {
            return 'foo';
        });

        $this->assertSame('foo', $actual);
    }

    public function testRegexify()
    {
        $this->assertSame('#^//?$#', Route\regexify('/'));
        $this->assertSame('#^/foo/?$#', Route\regexify('/foo'));
        $this->assertSame('#^/foo/bar/?$#', Route\regexify('/foo/bar'));
        $this->assertSame('#^/foo/(?<baz>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{baz}'));
        $this->assertSame('#^/foo/(?<BaZ>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{BaZ}'));
        $this->assertSame('#^/foo/(?<bar_baz>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{bar_baz}'));
        $this->assertSame('#^/foo/(?<baz>[A-z0-9_-]+)/qux/?$#', Route\regexify('/foo/{baz}/qux'));
        $this->assertSame('#^/foo/(?<baz>[A-z0-9_-]+)?/?$#', Route\regexify('/foo/{baz}?'));
    }

    public function testRoutify()
    {
        $this->assertSame(['get', '/'], Route\routify('\\index.get.php'));
        $this->assertSame(['get', '/'], Route\routify('index.get.php'));
        $this->assertSame(['get', '/'], Route\routify('/index.get.php'));
        $this->assertSame(['post', '/'], Route\routify('/index.post.php'));
        $this->assertSame(['get', '/foo'], Route\routify('/foo.get.php'));
        $this->assertSame(['get', '/foo'], Route\routify('/foo/index.get.php'));
        $this->assertSame(['get', '/foo/bar'], Route\routify('/foo.bar.get.php'));
        $this->assertSame(['get', '/foo/bar'], Route\routify('/foo/bar.get.php'));
        $this->assertSame(['get', '/foo/bar'], Route\routify('/foo/bar/index.get.php'));
        $this->assertSame(['get', '/foo/{id}'], Route\routify('/foo.{id}.get.php'));
        $this->assertSame(['get', '/foo/{id}'], Route\routify('/foo.$id.get.php'));
        $this->assertSame(['get', '/foo/?{id}?'], Route\routify('/foo.@id.get.php'));
    }

    public function testMatch()
    {
        $routes = [null, false];
        $this->assertFalse(Route\match($routes));

        $routes = [null, null];
        $this->assertNull(Route\match($routes));
    }

    /**
     * @runInSeparateProcess
     */
    public function testMethodPath()
    {
        $methodPath = Route\method_path(['OPTIONS', '/baz']);
        $this->assertSame(['OPTIONS', '/baz'], $methodPath);

        $serverRequest = new ServerRequest([], [], '/foo', 'PUT');
        $methodPath = Route\method_path($serverRequest);
        $this->assertSame(['PUT', '/foo'], $methodPath);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $methodPath = Route\method_path(null);
        $this->assertSame(['POST', '/bar/baz'], $methodPath);

        Container\set(SWOOLE_HTTP_REQUEST, new SwooleHttpRequestMock('DELETE', '/qux'));
        $methodPath = Route\method_path(null);
        $this->assertSame(['DELETE', '/qux'], $methodPath);
    }

    protected function setUp(): void
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
}
