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

        ['data' => $data, 'headers' => $headers] = fetch('https://postman-echo.com/get', ['query' => $args]);

        $this->assertSame($args, $data['args']);
    }
}
