<?php

namespace Siler\Test\Unit;

use Siler\Functional as f;

class FunctionalTest extends \PHPUnit\Framework\TestCase
{
    public function testId()
    {
        $id = f\identity();
        $this->assertSame('foo', $id('foo'));
    }

    public function testAlways()
    {
        $const = f\always('foo');
        $this->assertSame('foo', $const());
        $this->assertSame('foo', $const('bar'));
    }

    public function testEq()
    {
        $equalFoo = f\equal('foo');
        $equalOne = f\equal(1);

        $this->assertTrue($equalFoo('foo'));
        $this->assertTrue($equalOne(1));
        $this->assertFalse($equalOne('1'));
        $this->assertFalse($equalOne(true));
    }

    public function testLt()
    {
        $lessThanTwo = f\less_than(2);

        $this->assertTrue($lessThanTwo(1));
        $this->assertFalse($lessThanTwo(2));
        $this->assertFalse($lessThanTwo(3));
    }

    public function testGt()
    {
        $greaterThanTwo = f\greater_than(2);

        $this->assertFalse($greaterThanTwo(1));
        $this->assertFalse($greaterThanTwo(2));
        $this->assertTrue($greaterThanTwo(3));
    }

    public function testIfe()
    {
        $then = f\if_else(f\identity());
        $else = $then(f\always('foo'));
        $foo = $else(f\always('bar'));

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
        $lessThan2AndLessThan3 = f\all([f\less_than(2), f\less_than(3)]);
        $equalTo1AndGreaterThan1 = f\all([f\equal(1), f\greater_than(1)]);

        $this->assertTrue($lessThan2AndLessThan3(1));
        $this->assertFalse($equalTo1AndGreaterThan1(1));
    }

    public function testNot()
    {
        $notEqual2 = f\not(f\equal(2));

        $this->assertTrue($notEqual2(3));
        $this->assertFalse($notEqual2(2));
    }

    public function testMath()
    {
        $add1 = f\add(1);
        $this->assertSame(2, $add1(1));

        $sub2 = f\sub(2);
        $this->assertSame(1, $sub2(3));

        $mul2 = f\mul(2);
        $this->assertSame(4, $mul2(2));

        $div2 = f\div(2);
        $this->assertSame(2, $div2(4));

        $sub3 = f\sub(3);
        $this->assertSame(-1, $sub3(2));

        $div4 = f\div(4);
        $this->assertSame(.5, $div4(2));

        $mod3 = f\mod(3);
        $this->assertSame(2, $mod3(5));

        $modNeg3 = f\mod(-3);
        $this->assertSame(2, $modNeg3(5));

        $this->assertSame(-2, $mod3(-5));
        $this->assertSame(-2, $modNeg3(-5));
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
        $bool = f\bool();

        $this->assertTrue($bool(true));
        $this->assertTrue($bool('foo'));
        $this->assertTrue($bool(1));

        $this->assertFalse($bool(false));
        $this->assertFalse($bool(''));
        $this->assertFalse($bool(0));
    }

    public function testNoop()
    {
        $noop = f\noop();
        $noop();

        $this->assertTrue(true);
    }

    public function testHold()
    {
        $this->expectOutputString('foo');

        $echoFoo = function ($val) {
            echo $val;
        };

        $then = f\if_else(f\bool());
        $else = $then(f\hold($echoFoo));
        $ifelse = $else(f\noop());

        $ifelse('foo');
    }

    public function testPuts()
    {
        $this->expectOutputString('foo');

        $putsFoo = f\puts('foo');
        $putsFoo();
    }

    public function testFlatten()
    {
        $input = [0, 1, [2, 3], [4, 5], [6, [7, 8, [9]]]];
        $expected = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $actual = f\flatten($input);

        $this->assertEquals($expected, $actual);
    }

    public function testHead()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 1;
        $actual = f\head($input);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([1, 2, 3, 4, 5], $input);
    }

    public function testLast()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = 5;
        $actual = f\last($input);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([1, 2, 3, 4, 5], $input);
    }

    public function testTail()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [2, 3, 4, 5];
        $actual = f\tail($input);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([1, 2, 3, 4, 5], $input);
    }

    public function testInit()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, 2, 3, 4];
        $actual = f\init($input);

        $this->assertEquals($expected, $actual);
        $this->assertEquals([1, 2, 3, 4, 5], $input);
    }

    public function testUncons()
    {
        $input = [1, 2, 3, 4, 5];
        $expected = [1, [2, 3, 4, 5]];

        list($head, $tail) = f\uncons($input);
        $actual = [$head, $tail];

        $this->assertEquals($expected, $actual);
        $this->assertEquals([1, 2, 3, 4, 5], $input);
    }
}
