<?php

use PHPUnit\Framework\TestCase;

class SilerTest extends TestCase
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
        $this->assertEquals($_GET, Siler\get());
        $this->assertEquals('bar', Siler\get('foo'));
        $this->assertEquals('qux', Siler\get('baz', 'qux'));
        $this->assertNull(Siler\get('baz'));
    }

    public function testPost()
    {
        $this->assertEquals($_POST, Siler\post());
        $this->assertEquals('bar', Siler\post('foo'));
        $this->assertEquals('qux', Siler\post('baz', 'qux'));
        $this->assertNull(Siler\post('baz'));
    }

    public function testInput()
    {
        $this->assertEquals($_REQUEST, Siler\input());
        $this->assertEquals('bar', Siler\input('foo'));
        $this->assertEquals('qux', Siler\input('baz', 'qux'));
        $this->assertNull(Siler\input('baz'));
    }

    public function testUrl()
    {
        $this->assertEquals('/foo/qux', Siler\url('/qux'));
        $this->assertEquals('/foo/', Siler\url());
    }

    public function testPath()
    {
        $this->assertEquals('/bar/baz', Siler\path());
    }

    public function testUri()
    {
        $this->assertEquals('http://test:8000/bar/baz', Siler\uri());
    }

    public function testRoute()
    {
        Siler\route('/^\/bar\/([a-z]+)$/', function ($params) {
            $this->assertEquals('baz', $params[1]);
        });
    }

    public function testRequestMethodIs()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Siler\is_post());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Siler\is_get());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue(Siler\is_put());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue(Siler\is_delete());

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertTrue(Siler\is_options());

        $_SERVER['REQUEST_METHOD'] = 'CUSTOM';
        $this->assertTrue(Siler\request_method_is('custom'));
    }

    public function testRequireFn()
    {
        $cb = Siler\require_fn(__DIR__.'/fixtures/foo.php');
        $this->assertEquals('baz', $cb(['bar' => 'baz']));
    }

    public function testDump()
    {
        $expected = "<pre>string(4) \"test\"\n</pre>";

        ob_start();

        Siler\dump('test');

        $actual = ob_get_contents();

        ob_end_clean();

        $this->assertEquals($expected, $actual);
    }
}
