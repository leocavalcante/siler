<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Siler\Tuple as T;

class TupleTest extends TestCase
{
    public function testTuple()
    {
        $tuple = T\tuple(1, 'a', true);

        $this->assertFalse(is_array($tuple));
        $this->assertSame(1, $tuple[0]);
        $this->assertSame('a', $tuple[1]);
        $this->assertSame(true, $tuple[2]);
        $this->assertTrue(isset($tuple[1]));
        $this->assertIsArray($tuple->values());
        $this->assertSame([1, 'a', true], $tuple->values());
    }

    public function testOutOfRangeGet()
    {
        $this->expectException(OutOfRangeException::class);
        $tuple = T\tuple(1);
        $tuple[1];
    }

    public function testImmutableSet()
    {
        $this->expectException(RuntimeException::class);
        $tuple = T\tuple(1);
        $tuple[1] = 2;
    }

    public function testImmutableUnset()
    {
        $this->expectException(RuntimeException::class);
        $tuple = T\tuple(1);
        unset($tuple[0]);
    }

    public function testCount()
    {
        $tuple = T\tuple(1, 2, 3);
        $this->assertCount(3, $tuple);
    }
}
