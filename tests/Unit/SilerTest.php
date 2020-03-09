<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use function Siler\array_get;
use function Siler\array_get_bool;
use function Siler\array_get_float;
use function Siler\array_get_int;
use function Siler\array_get_str;
use function Siler\require_fn;
use const Siler\ARRAY_GET_ERROR_MESSAGE;

class SilerTest extends TestCase
{
    public function testArrayGet()
    {
        $fixture = ['foo' => 'bar'];
        $this->assertSame('bar', array_get($fixture, 'foo'));
        $this->assertSame('qux', array_get($fixture, 'baz', 'qux'));
        $this->assertNull(array_get($fixture, 'foobar'));
    }

    public function testArrayGetNullArray()
    {
        $fixture = null;
        $this->assertNull(array_get($fixture, 'foobar'));
        $this->assertSame('qux', array_get($fixture, 'baz', 'qux'));
    }

    public function testArrayGetCaseSensitive()
    {
        $fixture = ['Foo' => 'bar'];
        $this->assertNull(array_get($fixture, 'foo'));
    }

    public function testArrayGetCaseInsensitive()
    {
        $fixture = ['Foo' => 'bar'];
        $this->assertSame('bar', array_get($fixture, 'foo', null, true));
    }

    public function testArrayGetStr()
    {
        $fixture = ['foo' => 'bar'];
        $this->assertSame('bar', array_get_str($fixture, 'foo'));

        $fixture = ['foo' => 1];
        $this->assertSame('1', array_get_str($fixture, 'foo'));

        $fixture = [];
        $this->assertSame('bar', array_get_str($fixture, 'foo', 'bar'));

        $this->expectException(UnexpectedValueException::class);
        $fixture = [];
        array_get_str($fixture, 'foo');
    }

    public function testArrayGetInt()
    {
        $fixture = ['foo' => 1];
        $this->assertSame(1, array_get_int($fixture, 'foo'));

        $fixture = ['foo' => '1'];
        $this->assertSame(1, array_get_int($fixture, 'foo'));

        $fixture = [];
        $this->assertSame(1, array_get_int($fixture, 'foo', 1));

        $this->expectException(UnexpectedValueException::class);
        $fixture = [];
        array_get_int($fixture, 'foo');
    }

    public function testArrayGetFloat()
    {
        $fixture = ['foo' => 1.1];
        $this->assertSame(1.1, array_get_float($fixture, 'foo'));

        $fixture = ['foo' => '1.1'];
        $this->assertSame(1.1, array_get_float($fixture, 'foo'));

        $fixture = [];
        $this->assertSame(1.1, array_get_float($fixture, 'foo', 1.1));

        $this->expectException(UnexpectedValueException::class);
        $fixture = [];
        array_get_float($fixture, 'foo');
    }

    public function testArrayGetBool()
    {
        $fixture = ['foo' => true];
        $this->assertSame(true, array_get_bool($fixture, 'foo'));

        $fixture = ['foo' => 'true'];
        $this->assertSame(true, array_get_bool($fixture, 'foo'));

        $fixture = [];
        $this->assertSame(true, array_get_bool($fixture, 'foo', true));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(ARRAY_GET_ERROR_MESSAGE, 'foo'));
        $fixture = [];
        array_get_bool($fixture, 'foo');
    }

    public function testRequireFn()
    {
        $cb = require_fn(__DIR__ . '/../fixtures/foo.php');
        $this->assertSame('baz', $cb(['bar' => 'baz']));

        $cb = require_fn('dont exists');
        $this->assertNull($cb());

        $cb = require_fn(__DIR__ . '/../fixtures/callable_require.php');
        $this->assertSame('bar', $cb(['foo' => 'bar']));
    }
}
