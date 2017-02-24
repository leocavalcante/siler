<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Http\Request;

class RequestTest extends TestCase
{
    protected function setUp()
    {
        $_GET = $_POST = $_REQUEST = $_COOKIE = $_SESSION = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['PATH_INFO'] = '/bar/baz';
        $_SERVER['NON_HTTP'] = 'Ignore me';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'phpunit/test';
    }

    public function testRaw()
    {
        $rawContent = Request\raw(__DIR__.'/../fixtures/php_input.txt');
        $this->assertEquals('foo=bar', $rawContent);
    }

    public function testParams()
    {
        $params = Request\params(__DIR__.'/../fixtures/php_input.txt');

        $this->assertArrayHasKey('foo', $params);
        $this->assertContains('bar', $params);
        $this->assertCount(1, $params);
        $this->assertArraySubset(['foo' => 'bar'], $params);
    }

    public function testJson()
    {
        $params = Request\json(__DIR__.'/../fixtures/php_input.json');

        $this->assertArrayHasKey('foo', $params);
        $this->assertContains('bar', $params);
        $this->assertCount(1, $params);
        $this->assertArraySubset(['foo' => 'bar'], $params);
    }

    public function testHeaders()
    {
        $headers = Request\headers();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Host', $headers);
        $this->assertContains('phpunit/test', $headers);
        $this->assertContains('test:8000', $headers);
        $this->assertCount(2, $headers);
        $this->assertArraySubset([
            'Content-Type' => 'phpunit/test',
            'Host' => 'test:8000',
        ], $headers);
    }

    public function testHeader()
    {
        $contentType = Request\header('Content-Type');
        $this->assertEquals('phpunit/test', $contentType);
    }

    public function testGet()
    {
        $this->assertEquals($_GET, Request\get());
        $this->assertEquals('bar', Request\get('foo'));
        $this->assertEquals('qux', Request\get('baz', 'qux'));
        $this->assertNull(Request\get('baz'));
    }

    public function testPost()
    {
        $this->assertEquals($_POST, Request\post());
        $this->assertEquals('bar', Request\post('foo'));
        $this->assertEquals('qux', Request\post('baz', 'qux'));
        $this->assertNull(Request\post('baz'));
    }

    public function testInput()
    {
        $this->assertEquals($_REQUEST, Request\input());
        $this->assertEquals('bar', Request\input('foo'));
        $this->assertEquals('qux', Request\input('baz', 'qux'));
        $this->assertNull(Request\input('baz'));
    }

    public function testRequestMethodIs()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Request\is_post());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request\is_get());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue(Request\is_put());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue(Request\is_delete());

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertTrue(Request\is_options());

        $_SERVER['REQUEST_METHOD'] = 'CUSTOM';
        $this->assertTrue(Request\method_is('custom'));
    }
}
