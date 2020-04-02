<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;

/**
 * Class FromToArrayTest
 * @package Siler\Test\Unit\Prelude
 */
class FromToArrayTest extends TestCase
{
    public function testFromArray()
    {
        $fixture = FromToArrayFixture::fromArray([
            'foo' => 'foo',
            'fooBar' => 'bar',
            'foo_bar_baz' => 'baz',
        ]);

        $this->assertSame('foo', $fixture->foo);
        $this->assertSame('bar', $fixture->fooBar);
        $this->assertSame('baz', $fixture->fooBarBaz);
    }

    public function testToArray()
    {
        $fixture = new FromToArrayFixture();
        $fixture->foo = 'foo';
        $fixture->fooBar = 'bar';

        $arr = $fixture->toArray();
        $this->assertSame('foo', $arr['foo']);
        $this->assertSame('bar', $arr['foo_bar']);
        $this->assertNull($arr['foo_bar_baz'] ?? null);
        $this->assertNull($arr['fooBarBaz'] ?? null);

        $arr = $fixture->toArray(false);
        $this->assertSame('foo', $arr['foo']);
        $this->assertSame('bar', $arr['fooBar']);
        $this->assertNull($arr['foo_bar'] ?? null);
    }
}
