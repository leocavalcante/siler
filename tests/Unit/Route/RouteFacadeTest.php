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
        $_SERVER['REQUEST_URI'] = '/bar/baz';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testGet()
    {
        $this->expectOutputString('test get');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        Route\get('/bar/baz', function ($params) {
            echo 'test get';
        });
    }

    public function testPost()
    {
        $this->expectOutputString('test post');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\post('/bar/baz', function ($params) {
            echo 'test post';
        });
    }

    public function testPut()
    {
        $this->expectOutputString('test put');

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        Route\put('/bar/baz', function ($params) {
            echo 'test put';
        });
    }

    public function testDelete()
    {
        $this->expectOutputString('test delete');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        Route\delete('/bar/baz', function ($params) {
            echo 'test delete';
        });
    }

    public function testOptions()
    {
        $this->expectOutputString('test options');

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        Route\options('/bar/baz', function ($params) {
            echo 'test options';
        });
    }
}
