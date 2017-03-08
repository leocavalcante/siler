<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
        $_SERVER['REQUEST_METHOD'] = 'GET';
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
        Route\route('get', '/bar/{bar}', __DIR__.'/../../fixtures/to_be_required.php');
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

    public function testRegexify()
    {
        $this->assertEquals('#^//?$#', Route\regexify('/'));
        $this->assertEquals('#^/foo/?$#', Route\regexify('/foo'));
        $this->assertEquals('#^/foo/bar/?$#', Route\regexify('/foo/bar'));
        $this->assertEquals('#^/foo/(?<baz>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{baz}'));
        $this->assertEquals('#^/foo/(?<BaZ>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{BaZ}'));
        $this->assertEquals('#^/foo/(?<bar_baz>[A-z0-9_-]+)/?$#', Route\regexify('/foo/{bar_baz}'));
        $this->assertEquals('#^/foo/(?<baz>[A-z0-9_-]+)/qux/?$#', Route\regexify('/foo/{baz}/qux'));
    }

    public function testRoutify()
    {
        $this->assertEquals(['get', '/'], Route\routify('\\index.get.php'));
        $this->assertEquals(['get', '/'], Route\routify('index.get.php'));
        $this->assertEquals(['get', '/'], Route\routify('/index.get.php'));
        $this->assertEquals(['post', '/'], Route\routify('/index.post.php'));
        $this->assertEquals(['get', '/foo'], Route\routify('/foo.get.php'));
        $this->assertEquals(['get', '/foo'], Route\routify('/foo/index.get.php'));
        $this->assertEquals(['get', '/foo/bar'], Route\routify('/foo.bar.get.php'));
        $this->assertEquals(['get', '/foo/bar'], Route\routify('/foo/bar.get.php'));
        $this->assertEquals(['get', '/foo/bar'], Route\routify('/foo/bar/index.get.php'));
        $this->assertEquals(['get', '/foo/{id}'], Route\routify('/foo.{id}.get.php'));
    }
}
