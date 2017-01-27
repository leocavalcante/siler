<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use function Siler\Route\route;
use function Siler\Route\regexify;

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

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testRouteMatching()
    {
        route('get', '/foo', function ($params) {
            throw new \Exception('Route /foo should not match');
        });

        route('get', '/bar', function ($params) {
            throw new \Exception('Route /bar should not match');
        });

        route('get', '/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteRegexp()
    {
        route('get', '/bar/([a-z]+)', function ($params) {
            throw new \Exception($params[1]);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteNamedGroup()
    {
        route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteWrappedNamedGroup()
    {
        $_SERVER['REQUEST_URI'] = '/bar/baz/qux';

        route('get', '/bar/{baz}', function ($params) {
            throw new \Exception('I should not be called');
        });

        route('get', '/bar/{baz}/qux', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz-qux
     */
    public function testRouteNamedGroupWithDash()
    {
        $_SERVER['REQUEST_URI'] = '/bar/baz-qux';

        route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz-2017
     */
    public function testRouteNamedGroupWithNumber()
    {
        $_SERVER['REQUEST_URI'] = '/bar/baz-2017';

        route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz_qux
     */
    public function testRouteNamedGroupWithUnderscore()
    {
        $_SERVER['REQUEST_URI'] = '/bar/baz_qux';

        route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Throw was required
     */
    public function testRouteWithString()
    {
        route('get', '/bar/{bar}', __DIR__.'/../fixtures/throw.php');
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route POST /bar/baz should match
     */
    public function testRouteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        route('get', '/bar/baz', function ($params) {
            throw new \Exception('Route GET /bar/baz should not match');
        });

        route('post', '/bar/baz', function ($params) {
            throw new \Exception('Route POST /bar/baz should match');
        });
    }

    public function testRegexify()
    {
        $this->assertEquals('#^//?$#', regexify('/'));
        $this->assertEquals('#^/foo/?$#', regexify('/foo'));
        $this->assertEquals('#^/foo/bar/?$#', regexify('/foo/bar'));
        $this->assertEquals('#^/foo/(?<baz>[A-z0-9_-]+)/?$#', regexify('/foo/{baz}'));
        $this->assertEquals('#^/foo/(?<BaZ>[A-z0-9_-]+)/?$#', regexify('/foo/{BaZ}'));
        $this->assertEquals('#^/foo/(?<bar_baz>[A-z0-9_-]+)/?$#', regexify('/foo/{bar_baz}'));
        $this->assertEquals('#^/foo/(?<baz>[A-z0-9_-]+)/qux/?$#', regexify('/foo/{baz}/qux'));
    }
}
