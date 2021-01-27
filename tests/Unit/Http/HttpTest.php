<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Http;

class HttpTest extends TestCase
{
    public function testCookie()
    {
        $this->assertSame($_COOKIE, Http\cookie());
        $this->assertSame('bar', Http\cookie('foo'));
        $this->assertSame('qux', Http\cookie('baz', 'qux'));
        $this->assertNull(Http\cookie('baz'));
    }

    public function testSession()
    {
        $this->assertSame($_SESSION, Http\session());
        $this->assertSame('bar', Http\session('foo'));
        $this->assertSame('qux', Http\session('baz', 'qux'));
        $this->assertNull(Http\session('baz'));
    }

    public function testSetsession()
    {
        Http\setsession('baz', 'qux');

        $this->assertArrayHasKey('baz', $_SESSION);
        $this->assertSame('qux', $_SESSION['baz']);
    }

    public function testFlash()
    {
        $actual = Http\flash('foo');

        $this->assertSame('bar', $actual);
        $this->assertNull(Http\session('foo'));
    }

    public function testUrl()
    {
        $this->assertSame('/foo/qux', Http\url('/qux'));
        $this->assertSame('/foo/', Http\url());
    }

    public function testPath()
    {
        $this->assertSame('/bar/baz', Http\path());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotInSubFolderPath()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $this->assertSame('/foo/bar', Http\path());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSubFolderPathRepeats()
    {
        $_SERVER['REQUEST_URI'] = '/bar/foo/baz';

        $this->assertSame('/bar/foo/baz', Http\path());
    }

    public function testUri()
    {
        $this->assertSame('http://test:8000/bar/baz', Http\uri());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirect()
    {
        Http\redirect('test://siler');

        if (function_exists('xdebug_get_headers')) {
            $headers = xdebug_get_headers();
            $this->assertContains('Location: test://siler', $headers);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testFuzzyQueryString()
    {
        $_SERVER['QUERY_STRING'] = 'baz=qux&foo=bar';
        $this->assertSame('/bar/baz', Http\path());
    }

    protected function setUp(): void
    {
        $_GET = $_POST = $_REQUEST = $_COOKIE = $_SESSION = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['REQUEST_URI'] = '/bar/baz?foo=bar';
    }
}
