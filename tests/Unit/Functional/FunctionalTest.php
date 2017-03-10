<?php

namespace Siler\Test\Unit;

use Siler\Functional as f;

class FunctionalTest extends \PHPUnit\Framework\TestCase
{
    public function testId()
    {
        $this->assertSame('foo', f\identity()('foo'));
    }

    public function testAlways()
    {
        $this->assertSame('foo', f\always('foo')());
        $this->assertSame('foo', f\always('foo')('bar'));
    }

    public function testEq()
    {
        $this->assertTrue(f\equal('foo')('foo'));
        $this->assertTrue(f\equal(1)(1));
        $this->assertFalse(f\equal(1)('1'));
        $this->assertFalse(f\equal(1)(true));
    }

    public function testLt()
    {
        $this->assertTrue(f\less_than(2)(1));
        $this->assertFalse(f\less_than(2)(2));
        $this->assertFalse(f\less_than(2)(3));
    }

    public function testGt()
    {
        $this->assertFalse(f\greater_than(2)(1));
        $this->assertFalse(f\greater_than(2)(2));
        $this->assertTrue(f\greater_than(2)(3));
    }

    public function testIfe()
    {
        $foo = f\if_else(f\identity())(f\always('foo'))(f\always('bar'));
        $this->assertSame('foo', $foo(true));
    }

    public function testMatch()
    {
        $test = f\match([
            [f\equal('foo'), f\always('bar')],
            [f\equal('bar'), f\always('baz')],
            [f\equal('baz'), f\always('qux')],
        ]);

        $this->assertSame('bar', $test('foo'));
        $this->assertSame('baz', $test('bar'));
        $this->assertSame('qux', $test('baz'));
        $this->assertNull($test('qux'));
    }

    public function testAny()
    {
        $test = f\any([f\equal(2), f\greater_than(2)]);

        $this->assertFalse($test(1));
        $this->assertTrue($test(2));
        $this->assertTrue($test(3));
    }

    public function testAll()
    {
        $this->assertTrue(f\all([f\less_than(2), f\less_than(3)])(1));
        $this->assertFalse(f\all([f\equal(1), f\greater_than(1)])(1));
    }

    public function testNot()
    {
        $this->assertTrue(f\not(f\equal(2))(3));
        $this->assertFalse(f\not(f\equal(2))(2));
    }

    public function testMath()
    {
        $this->assertSame(2, f\add(1)(1));
        $this->assertSame(1, f\sub(2)(3));
        $this->assertSame(4, f\mul(2)(2));
        $this->assertSame(2, f\div(2)(4));
        $this->assertSame(-1, f\sub(3)(2));
        $this->assertSame(.5, f\div(4)(2));
        $this->assertSame(2, f\mod(3)(5));
        $this->assertSame(2, f\mod(-3)(5));
        $this->assertSame(-2, f\mod(3)(-5));
        $this->assertSame(-2, f\mod(-3)(-5));
    }

    public function testCompose()
    {
        $test = f\compose([f\add(2), f\mul(2)]);
        $this->assertSame(8, $test(2));

        $test = f\compose([f\div(2), f\sub(1)]);
        $this->assertSame(0, $test(2));
    }

    public function testBool()
    {
        $this->assertTrue(f\bool()(true));
        $this->assertTrue(f\bool()('foo'));
        $this->assertTrue(f\bool()(1));

        $this->assertFalse(f\bool()(false));
        $this->assertFalse(f\bool()(''));
        $this->assertFalse(f\bool()(0));
    }

    public function testNoop()
    {
        f\noop()();
        $this->assertTrue(true);
    }

    public function testHold()
    {
        $this->expectOutputString('foo');

        $echoFoo = function ($val) {
            echo $val;
        };

        f\if_else(f\bool())(f\hold($echoFoo))(f\noop())('foo');
    }

    public function testPuts()
    {
        $this->expectOutputString('foo');
        f\puts('foo')();
    }
}
