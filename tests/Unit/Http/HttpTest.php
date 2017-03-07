<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Http;

class HttpTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = $_COOKIE = $_SESSION = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz';
    }

    public function testCookie()
    {
        $this->assertEquals($_COOKIE, Http\cookie());
        $this->assertEquals('bar', Http\cookie('foo'));
        $this->assertEquals('qux', Http\cookie('baz', 'qux'));
        $this->assertNull(Http\cookie('baz'));
    }

    public function testSession()
    {
        $this->assertEquals($_SESSION, Http\session());
        $this->assertEquals('bar', Http\session('foo'));
        $this->assertEquals('qux', Http\session('baz', 'qux'));
        $this->assertNull(Http\session('baz'));
    }

    public function testSetsession()
    {
        Http\setsession('baz', 'qux');

        $this->assertArrayHasKey('baz', $_SESSION);
        $this->assertArraySubset(['baz' => 'qux'], $_SESSION);
    }

    public function testFlash()
    {
        $actual = Http\flash('foo');

        $this->assertEquals('bar', $actual);
        $this->assertNull(Http\session('foo'));
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

    /**
     * @runInSeparateProcess
     */
    public function testNotInSubfolderPath()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $this->assertEquals('/foo/bar', Http\path());
    }

    public function testUri()
    {
        $this->assertEquals('http://test:8000/bar/baz', Http\uri());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirect()
    {
        Http\redirect('test://siler');

        $headers = xdebug_get_headers();

        $this->assertContains('Location: test://siler', $headers);
    }
}
