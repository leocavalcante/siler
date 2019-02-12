<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;

class SilerTest extends TestCase
{


    public function testArrayGet()
    {
        $fixture = ['foo' => 'bar'];
        $this->assertSame('bar', \Siler\array_get($fixture, 'foo'));
        $this->assertSame('qux', \Siler\array_get($fixture, 'baz', 'qux'));
        $this->assertNull(\Siler\array_get($fixture, 'foobar'));
    }


    public function testArrayGetNullArray()
    {
        $fixture = null;
        $this->assertNull(\Siler\array_get($fixture, 'foobar'));
        $this->assertSame('qux', \Siler\array_get($fixture, 'baz', 'qux'));
    }


    public function testArrayGetCaseSensitive()
    {
        $fixture = ['Foo' => 'bar'];
        $this->assertNull(\Siler\array_get($fixture, 'foo'));
    }


    public function testArrayGetCaseInsensitive()
    {
        $fixture = ['Foo' => 'bar'];
        $this->assertSame('bar', \Siler\array_get($fixture, 'foo', null, true));
    }


    public function testRequireFn()
    {
        $cb = \Siler\require_fn(__DIR__ . '/../fixtures/foo.php');
        $this->assertSame('baz', $cb(['bar' => 'baz']));

        $cb = \Siler\require_fn('dont exists');
        $this->assertNull($cb());

        $cb = \Siler\require_fn(__DIR__ . '/../fixtures/callable_require.php');
        $this->assertSame('bar', $cb(['foo' => 'bar']));
    }
}
