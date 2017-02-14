<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['PATH_INFO'] = '/bar/baz';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testRouteMatching()
    {
        Route\route('get', '/foo', function ($params) {
            throw new \Exception('Route /foo should not match');
        });

        Route\route('get', '/bar', function ($params) {
            throw new \Exception('Route /bar should not match');
        });

        Route\route('get', '/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteRegexp()
    {
        Route\route('get', '/bar/([a-z]+)', function ($params) {
            throw new \Exception($params[1]);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteNamedGroup()
    {
        Route\route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteWrappedNamedGroup()
    {
        $_SERVER['PATH_INFO'] = '/bar/baz/qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            throw new \Exception('I should not be called');
        });

        Route\route('get', '/bar/{baz}/qux', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz-qux
     */
    public function testRouteNamedGroupWithDash()
    {
        $_SERVER['PATH_INFO'] = '/bar/baz-qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz-2017
     */
    public function testRouteNamedGroupWithNumber()
    {
        $_SERVER['PATH_INFO'] = '/bar/baz-2017';

        Route\route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage baz_qux
     */
    public function testRouteNamedGroupWithUnderscore()
    {
        $_SERVER['PATH_INFO'] = '/bar/baz_qux';

        Route\route('get', '/bar/{baz}', function ($params) {
            throw new \Exception($params['baz']);
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage OK
     */
    public function testRouteDefaultPathInfo()
    {
        unset($_SERVER['PATH_INFO']);

        Route\route('get', '/', function ($params) {
            throw new \Exception('OK');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Throw was required
     */
    public function testRouteWithString()
    {
        Route\route('get', '/bar/{bar}', __DIR__.'/../fixtures/throw.php');
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route POST /bar/baz should match
     */
    public function testRouteMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\route('get', '/bar/baz', function ($params) {
            throw new \Exception('Route GET /bar/baz should not match');
        });

        Route\route('post', '/bar/baz', function ($params) {
            throw new \Exception('Route POST /bar/baz should match');
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
}
