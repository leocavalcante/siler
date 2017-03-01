<?php

namespace Siler\Test;

class FunctionalTest extends \PHPUnit\Framework\TestCase
{
    public function testId()
    {
        $this->assertSame('foo', λ\identity()('foo'));
    }

    public function testAlways()
    {
        $this->assertSame('foo', λ\always('foo')());
        $this->assertSame('foo', λ\always('foo')('bar'));
    }

    public function testEq()
    {
        $this->assertTrue(λ\equal('foo')('foo'));
        $this->assertTrue(λ\equal(1)(1));
        $this->assertFalse(λ\equal(1)('1'));
        $this->assertFalse(λ\equal(1)(true));
    }

    public function testLt()
    {
        $this->assertTrue(λ\less_than(2)(1));
        $this->assertFalse(λ\less_than(2)(2));
        $this->assertFalse(λ\less_than(2)(3));
    }

    public function testGt()
    {
        $this->assertFalse(λ\greater_than(2)(1));
        $this->assertFalse(λ\greater_than(2)(2));
        $this->assertTrue(λ\greater_than(2)(3));
    }

    public function testIfe()
    {
        $foo = λ\if_else(λ\identity())(λ\always('foo'))(λ\always('bar'));
        $this->assertSame('foo', $foo(true));
    }

    public function testMatch()
    {
        $test = λ\match([
            [λ\equal('foo'), λ\always('bar')],
            [λ\equal('bar'), λ\always('baz')],
            [λ\equal('baz'), λ\always('qux')],
        ]);

        $this->assertSame('bar', $test('foo'));
        $this->assertSame('baz', $test('bar'));
        $this->assertSame('qux', $test('baz'));
        $this->assertNull($test('qux'));
    }

    public function testAny()
    {
        $test = λ\any([λ\equal(2), λ\greater_than(2)]);

        $this->assertFalse($test(1));
        $this->assertTrue($test(2));
        $this->assertTrue($test(3));
    }

    public function testAll()
    {
        $this->assertTrue(λ\all([λ\less_than(2), λ\less_than(3)])(1));
        $this->assertFalse(λ\all([λ\equal(1), λ\greater_than(1)])(1));
    }

    public function testNot()
    {
        $this->assertTrue(λ\not(λ\equal(2))(3));
        $this->assertFalse(λ\not(λ\equal(2))(2));
    }

    public function testMath()
    {
        $this->assertSame(2, λ\add(1)(1));
        $this->assertSame(1, λ\sub(2)(3));
        $this->assertSame(4, λ\mul(2)(2));
        $this->assertSame(2, λ\div(2)(4));
        $this->assertSame(-1, λ\sub(3)(2));
        $this->assertSame(.5, λ\div(4)(2));
    }

    public function testCompose()
    {
        $test = λ\compose([λ\add(2), λ\mul(2)]);
        $this->assertSame(8, $test(2));

        $test = λ\compose([λ\div(2), λ\sub(1)]);
        $this->assertSame(0, $test(2));
    }
}
