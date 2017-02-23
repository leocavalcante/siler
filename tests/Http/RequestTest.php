<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Http\Request;

class RequestTest extends TestCase
{
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

    /**
     * @runInSeparateProcess
     */
    public function testHeaders()
    {
        $_SERVER = [
            'NON_HTTP' => 'Ignore me',
            'HTTP_CONTENT_TYPE' => 'phpunit/test',
        ];

        $headers = Request\headers();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertContains('phpunit/test', $headers);
        $this->assertCount(1, $headers);
        $this->assertArraySubset(['Content-Type' => 'phpunit/test'], $headers);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHeader()
    {
        $_SERVER = [
            'HTTP_CONTENT_TYPE' => 'phpunit/test',
        ];

        $contentType = Request\header('Content-Type');

        $this->assertEquals('phpunit/test', $contentType);
    }
}
