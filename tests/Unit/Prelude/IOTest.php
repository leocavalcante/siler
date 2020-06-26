<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use function Siler\IO\csv_to_array;
use function Siler\IO\fetch;
use function Siler\IO\println;

class IOTest extends TestCase
{
    public function testPrintln()
    {
        $this->expectOutputString('foo' . PHP_EOL);
        println('foo');
    }

    public function testCsvToArray()
    {
        $arr = csv_to_array(__DIR__ . '/../../fixtures/test.csv');
        $this->assertSame([['1', 'foo'], ['2', 'bar'], ['3', 'baz']], $arr);
    }

    public function testFetch()
    {
        $args = ['foo' => 'bar'];
        ['response' => $response, 'error' => $err, 'status' => $code] = fetch('https://postman-echo.com/get', ['query' => $args]);
        $this->assertNull($err);
        $this->assertSame(200, $code);
        $this->assertSame($args, $response['args']);

        ['status' => $code, 'error' => $err] = fetch('https://postman-echo.com/status/418');
        $this->assertNull($err);
        $this->assertSame(418, $code);

        ['response' => $response, 'error' => $err, 'status' => $code] = fetch('https://postman-echo.com/post', [
            'method' => 'post',
            'body' => 'foobar',
            'headers' => ['content-type' => 'text/plain'],
        ]);
        $this->assertNull($err);
        $this->assertSame(200, $code);
        $this->assertSame('foobar', $response['data']);
    }
}
