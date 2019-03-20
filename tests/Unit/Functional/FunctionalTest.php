<?php

declare(strict_types=1);

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
            [f\equal('baz'), f\always('qux')]
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
        $this->assertSame(0.5, f\div(4)(2));
        $this->assertSame(2, f\mod(3)(5));
        $this->assertSame(2, f\mod(-3)(5));
        $this->assertSame(-2, f\mod(3)(-5));
        $this->assertSame(-2, f\mod(-3)(-5));
    }

    public function testCompose()
    {
        $test = f\compose([f\add(2), f\mul(2)]);
        $this->assertSame(6, $test(2));

        $test = f\compose([f\div(2), f\sub(1)]);
        $this->assertSame(0.5, $test(2));
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

    public function testFlatten()
    {
        $input = [0, 1, [2, 3], [4, 5], [6, [7, 8, [9]]]];
        $expected = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $actual = f\flatten($input);

        $this->assertSame($expected, $actual);
    }

    public function testHead()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 1;
        $actual = f\head($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testLast()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 5;
        $actual = f\last($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testTail()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [2, 3, 4, 5];
        $actual = f\tail($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testInit()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, 2, 3, 4];
        $actual = f\init($input);

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testUncons()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, [2, 3, 4, 5]];

        list($head, $tail) = f\uncons($input);
        $actual = [$head, $tail];

        $this->assertSame($expected, $actual);
        $this->assertSame([1, 2, 3, 4, 5], $input);
    }

    public function testNonNull()
    {
        $input = [0, null, false, '', null];
        $this->assertSame([0, false, ''], f\non_null($input));
    }

    public function testNonEmpty()
    {
        $input = [0, 1, false, true, '', 'foo', null, [], ['bar']];
        $this->assertSame([1, true, 'foo', ['bar']], f\non_empty($input));
    }

    public function testPartial()
    {
        $add = function (int $a, int $b) {
            return $a + $b;
        };

        $add1 = f\partial($add, 1);
        $this->assertSame(2, $add1(1));

        $commaExplode = f\partial('explode', ',');
        $this->assertSame(['foo', 'bar'], $commaExplode('foo,bar'));
    }

    public function testIfThen()
    {
        $this->expectOutputString('if_then');
        f\if_then(f\always(true))(f\puts('if_then'));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(f\is_empty([])());
        $this->assertFalse(f\is_empty('[]')());
    }

    public function testIsNull()
    {
        $this->assertTrue(f\isnull(null)());
        $this->assertFalse(f\isnull([])());
    }
}
