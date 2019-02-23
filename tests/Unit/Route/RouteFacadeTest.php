<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST']   = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['PATH_INFO']   = '/bar/baz';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
    }

    public function testGet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        Route\get('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    public function testPost()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Route\post('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    public function testPut()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        Route\put('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    public function testDelete()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        Route\delete('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    public function testOptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        Route\options('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }

    public function testAny()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route /bar/baz should match');

        $_SERVER['REQUEST_METHOD'] = 'ANYTHING';

        Route\any('/bar/baz', function ($params) {
            throw new \Exception('Route /bar/baz should match');
        });
    }
}
