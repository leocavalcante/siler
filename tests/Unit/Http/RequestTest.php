<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Http\Request;
use Zend\Diactoros\ServerRequest;
use Siler\Test\Unit\Route\SwooleHttpRequestMock;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = $_POST = $_REQUEST = $_COOKIE = $_SESSION = $_FILES = ['foo' => 'bar'];

        $_SERVER['HTTP_HOST'] = 'test:8000';
        $_SERVER['SCRIPT_NAME'] = '/foo/test.php';
        $_SERVER['PATH_INFO'] = '/bar/baz';
        $_SERVER['NON_HTTP'] = 'Ignore me';
        $_SERVER['CONTENT_TYPE'] = 'phpunit/test';
        $_SERVER['CONTENT_LENGTH'] = '123';
    }

    public function testRaw()
    {
        $rawContent = Request\raw(__DIR__ . '/../../fixtures/php_input.txt');
        $this->assertSame('foo=bar', $rawContent);
    }

    public function testParams()
    {
        $params = Request\params(__DIR__ . '/../../fixtures/php_input.txt');

        $this->assertArrayHasKey('foo', $params);
        $this->assertContains('bar', $params);
        $this->assertCount(1, $params);
        $this->assertArrayHasKey('foo', $params);
        $this->assertSame('bar', $params['foo']);
    }

    public function testJson()
    {
        $params = Request\json(__DIR__ . '/../../fixtures/php_input.json');

        $this->assertArrayHasKey('foo', $params);
        $this->assertContains('bar', $params);
        $this->assertCount(1, $params);
        $this->assertArrayHasKey('foo', $params);
        $this->assertSame('bar', $params['foo']);

        $params = Request\json();
        $this->assertEmpty($params);
    }

    public function testHeaders()
    {
        $headers = Request\headers();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertArrayHasKey('Host', $headers);
        $this->assertContains('phpunit/test', $headers);
        $this->assertContains('test:8000', $headers);
        $this->assertCount(3, $headers);

        $expectedHeaders = [
            'Content-Type' => 'phpunit/test',
            'Content-Length' => '123',
            'Host' => 'test:8000'
        ];

        foreach ($expectedHeaders as $key => $value) {
            $this->assertArrayHasKey($key, $headers);
            $this->assertSame($value, $headers[$key]);
        }
    }

    public function testHeader()
    {
        $contentType = Request\header('Content-Type');
        $this->assertSame('phpunit/test', $contentType);
    }

    public function testGet()
    {
        $this->assertSame($_GET, Request\get());
        $this->assertSame('bar', Request\get('foo'));
        $this->assertSame('qux', Request\get('baz', 'qux'));
        $this->assertNull(Request\get('baz'));
    }

    public function testPost()
    {
        $this->assertSame($_POST, Request\post());
        $this->assertSame('bar', Request\post('foo'));
        $this->assertSame('qux', Request\post('baz', 'qux'));
        $this->assertNull(Request\post('baz'));
    }

    public function testInput()
    {
        $this->assertSame($_REQUEST, Request\input());
        $this->assertSame('bar', Request\input('foo'));
        $this->assertSame('qux', Request\input('baz', 'qux'));
        $this->assertNull(Request\input('baz'));
    }

    public function testFile()
    {
        $this->assertSame($_FILES, Request\file());
        $this->assertSame('bar', Request\file('foo'));
        $this->assertSame('qux', Request\file('baz', 'qux'));
        $this->assertNull(Request\file('baz'));
    }

    public function testMethod()
    {
        $this->assertSame('GET', Request\method());

        $_POST['_method'] = 'POST';

        $this->assertSame('POST', Request\method());

        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

        $this->assertSame('PUT', Request\method());

        unset($_POST['_method']);
        unset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
    }

    public function testRequestMethodIs()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Request\method_is('post'));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request\method_is('get'));

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertTrue(Request\method_is('put'));

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertTrue(Request\method_is('delete'));

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertTrue(Request\method_is('options'));

        $_SERVER['REQUEST_METHOD'] = 'CUSTOM';
        $this->assertTrue(Request\method_is('custom'));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request\method_is(['get', 'post']));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Request\method_is(['get', 'post']));

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertFalse(Request\method_is(['get', 'post']));
    }

    public function testAcceptedLocales()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.5';
        $this->assertSame('en-US', array_keys(Request\accepted_locales())[0]);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
        $this->assertEmpty(Request\accepted_locales());
    }

    public function testRecommendedLocale()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.5';
        $this->assertSame('en-US', Request\recommended_locale());

        $_GET['lang'] = 'fr';
        $this->assertSame('fr', Request\recommended_locale());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
        $this->assertSame('fr', Request\recommended_locale());

        unset($_GET['lang']);
        $this->assertSame('it', Request\recommended_locale('it'));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.5';
        $this->assertSame('en-US', Request\recommended_locale('it'));

        if (function_exists('locale_get_default')) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
            $this->assertSame(\locale_get_default(), Request\recommended_locale());
        }
    }

    public function testAuthorizationHeader()
    {
        $this->assertNull(Request\authorization_header());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic foo';
        $this->assertSame('Basic foo', Request\authorization_header());

        $request = new ServerRequest([], [], null, null, 'php://input', ['Authorization' => 'Basic foo']);
        $this->assertSame('Basic foo', Request\authorization_header($request));

        $request = new SwooleHttpRequestMock('GET', '/', ['authorization' => 'Basic foo']);
        Container\set(SWOOLE_HTTP_REQUEST, $request);
        $this->assertSame('Basic foo', Request\authorization_header($request));
        Container\clear(SWOOLE_HTTP_REQUEST);
    }

    public function testBearer()
    {
        $this->assertNull(Request\bearer());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic foo';
        $this->assertNull(Request\bearer());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer foo';
        $this->assertSame('foo', Request\bearer());

        $request = new ServerRequest([], [], null, null, 'php://input', ['Authorization' => 'Bearer foo']);
        $this->assertSame('foo', Request\bearer($request));

        $request = new SwooleHttpRequestMock('GET', '/', ['authorization' => 'Bearer foo']);
        Container\set(SWOOLE_HTTP_REQUEST, $request);
        $this->assertSame('foo', Request\bearer($request));
        Container\clear(SWOOLE_HTTP_REQUEST);
    }
}
