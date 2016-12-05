<?php

define('ENV_PATH', __DIR__);
require __DIR__.'/../siler.php';

class SilerTest extends \PHPUnit\Framework\TestCase
{
    public function testEnv()
    {
        $this->assertEquals($_SERVER, env());
        $this->assertEquals('bar', env('FOO'));
        $this->assertEquals('baz', env('BAR', 'baz'));
        $this->assertNull(env('BAR'));
    }

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

    public function testDump()
    {
        $this->expectOutputString("<pre>string(3) \"foo\"\n</pre>");
        dump('foo');
    }
}
