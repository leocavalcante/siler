<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;

use function Siler\array_get;
use function Siler\require_fn;

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
