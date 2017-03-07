<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Http\Response;

/**
 * @runTestsInSeparateProcesses
 */
class ResponseTest extends TestCase
{
    public function testDefaultOutput()
    {
        $this->expectOutputString('');

        Response\output();

        $this->assertEquals(204, http_response_code());
        $this->assertContains('Content-Type: text/plain;charset=utf-8', xdebug_get_headers());
    }

    public function testText()
    {
        $this->expectOutputString('foo');

        Response\text('foo');

        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/plain;charset=utf-8', xdebug_get_headers());
    }

    public function testHtml()
    {
        $this->expectOutputString('<a href="#"></a>');

        Response\html('<a href="#"></a>');

        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/html;charset=utf-8', xdebug_get_headers());
    }

    public function testJson()
    {
        $this->expectOutputString('{"foo":"bar","baz":true,"qux":2}');

        Response\json(['foo' => 'bar', 'baz' => true, 'qux' => 2]);

        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    public function testStatusCode()
    {
        $this->expectOutputString('{"error":true,"message":"test"}');

        Response\json(['error' => true, 'message' => 'test'], 400);

        $this->assertEquals(400, http_response_code());
    }

    public function testHeader()
    {
        Response\header('X-Foo', 'foo');
        Response\header('X-Bar', 'bar');
        Response\header('X-Bar', 'baz', false);

        $headers = xdebug_get_headers();

        $this->assertContains('X-Foo: foo', $headers);
        $this->assertContains('X-Bar: bar', $headers);
        $this->assertContains('X-Bar: baz', $headers);
    }
}
