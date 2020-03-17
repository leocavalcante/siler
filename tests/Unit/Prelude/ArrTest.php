<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
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
}
