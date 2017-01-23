<?php

use PHPUnit\Framework\TestCase;
use Siler\Http;

class HttpTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
    }

    public function testGet()
    {
        $this->assertEquals($_GET, Http\get());
        $this->assertEquals('bar', Http\get('foo'));
        $this->assertEquals('qux', Http\get('baz', 'qux'));
        $this->assertNull(Http\get('baz'));
    }

    public function testPost()
    {
        $this->assertEquals($_POST, Http\post());
        $this->assertEquals('bar', Http\post('foo'));
        $this->assertEquals('qux', Http\post('baz', 'qux'));
        $this->assertNull(Http\post('baz'));
    }

    public function testInput()
    {
        $this->assertEquals($_REQUEST, Http\input());
        $this->assertEquals('bar', Http\input('foo'));
        $this->assertEquals('qux', Http\input('baz', 'qux'));
        $this->assertNull(Http\input('baz'));
    }

    public function testUrl()
    {
        $this->assertEquals('/foo/qux', Http\url('/qux'));
        $this->assertEquals('/foo/', Http\url());
    }

    public function testPath()
    {
        $this->assertEquals('/bar/baz', Http\path());
    }

    public function testUri()
    {
        $this->assertEquals('http://test:8000/bar/baz', Http\uri());
    }

    public function testRoute()
    {
        Http\route('/^\/bar\/([a-z]+)$/', function ($params) {
            $this->assertEquals('baz', $params[1]);
        });
    }

    public function testRequestMethodIs()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Http\is_post());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Http\is_get());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue(Http\is_put());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue(Http\is_delete());

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertTrue(Http\is_options());

        $_SERVER['REQUEST_METHOD'] = 'CUSTOM';
        $this->assertTrue(Http\method_is('custom'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirect()
    {
        Http\redirect('test://siler');
    }
}
