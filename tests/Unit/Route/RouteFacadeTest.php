<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteFacadeTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['PATH_INFO'] = '/bar/baz';
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        Route\get('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\post('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testPut()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        Route\put('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testDelete()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        Route\delete('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Route /bar/baz should match
     */
    public function testOptions()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        Route\options('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }
}
