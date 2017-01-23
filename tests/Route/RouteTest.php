<?php

use PHPUnit\Framework\TestCase;
use function Siler\Route\route;

class RouteTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testRouteMatching()
    {
        route('/foo', function ($params) {
            throw new Exception('Route /foo should not match');
        });

        route('/bar', function ($params) {
            throw new Exception('Route /bar should not match');
        });

        route('/bar/baz', function ($params) {
            throw new Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteRegexp()
    {
        route('/bar/([a-z]+)', function ($params) {
            throw new Exception($params[1]);
        });
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage baz
     */
    public function testRouteNamedGroup()
    {
        route('/bar/{baz}', function ($params) {
            throw new Exception($params['baz']);
        });
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Throw was required
     */
    public function testRouteWithString()
    {
        route('/bar/{bar}', __DIR__.'/../fixtures/throw.php');
    }
}
