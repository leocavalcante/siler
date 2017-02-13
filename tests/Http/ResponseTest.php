<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Http\Response;

class ResponseTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testDefaultOutput()
    {
        $this->expectOutputString('');
        Response\output();
        $this->assertEquals(204, http_response_code());
        $this->assertContains('Content-Type: text/plain;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testText()
    {
        $this->expectOutputString('foo');
        Response\text('foo');
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/plain;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testHtml()
    {
        $this->expectOutputString('&lt;a href=&quot;#&quot;&gt;&lt;/a&gt;');
        Response\html('<a href="#"></a>');
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/html;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testJson()
    {
        $this->expectOutputString('{"foo":"bar","baz":true,"qux":2}');
        Response\json(['foo' => 'bar', 'baz' => true, 'qux' => 2]);
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: application/json;charset=utf-8', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     */
    public function testStatusCode()
    {
        $this->expectOutputString('{"error":true,"message":"test"}');
        Response\json(['error' => true, 'message' => 'test'], 400);
        $this->assertEquals(400, http_response_code());
    }
}
