<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use function Siler\Arr\assoc;
use function Siler\Arr\set;

class ArrTest extends TestCase
{
    public function testSet()
    {
        $fixture = ['foo' => ['bar' => 'baz']];
        $expected = ['foo' => ['bar' => 'qux']];

        set($fixture, 'foo.bar', 'qux');

        $this->assertSame($expected, $fixture);
    }

    public function testSetDeeplyCreates()
    {
        $fixture = ['foo' => []];
        $expected = ['foo' => ['bar' => 'qux']];

        set($fixture, 'foo.bar', 'qux');

        $this->assertSame($expected, $fixture);
    }

    public function testAssoc()
    {
        $fixture = [
            ['foo', 'bar'],
            ['baz', 'qux'],
            [4, 2],
        ];

        $expected = [
            ['foo' => 'baz', 'bar' => 'qux'],
            ['foo' => 4, 'bar' => 2],
        ];

        $actual = assoc($fixture);
        $this->assertSame($expected, $actual);
    }
}
