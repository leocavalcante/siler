<?php

use PHPUnit\Framework\TestCase;

class SilerTest extends TestCase
{
    public function testGet()
    {
        $_GET = ['foo' => 'bar'];

        $this->assertEquals($_GET, get());
        $this->assertEquals('bar', get('foo'));
        $this->assertEquals('qux', get('baz', 'qux'));
        $this->assertNull(get('baz'));
    }

    public function testPost()
    {
        $_POST = ['foo' => 'bar'];

        $this->assertEquals($_POST, post());
        $this->assertEquals('bar', post('foo'));
        $this->assertEquals('qux', post('baz', 'qux'));
        $this->assertNull(post('baz'));
    }

    public function testInput()
    {
        $_REQUEST = ['foo' => 'bar'];

        $this->assertEquals($_REQUEST, input());
        $this->assertEquals('bar', input('foo'));
        $this->assertEquals('qux', input('baz', 'qux'));
        $this->assertNull(input('baz'));
    }

    public function testUrl()
    {
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $this->assertEquals('/foo/bar', url('/bar'));
    }

    public function testPath()
    {
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar';

        $this->assertEquals('/bar', path());
    }

    public function testUri()
    {
        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['REQUEST_URI'] = '/foo';

        $this->assertEquals('http://test:8000/foo', uri());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Path /foo
     */
    public function testStaticPath()
    {
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['REQUEST_URI'] = '/foo';

        static_path('/foo', function () {
            throw new Exception('Path /foo');
        });
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Path /foo/bar
     */
    public function testRegexpPath()
    {
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        regexp_path('/^\/foo\/([a-z]+)$/', function ($params) {
            throw new Exception('Path /foo/'.$params[1]);
        });
    }

    public function testRequestMethodIs()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(is_post());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(is_get());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue(is_put());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue(is_delete());

        $_SERVER['REQUEST_METHOD'] = 'CUSTOM';
        $this->assertTrue(request_method_is('custom'));
    }
}
