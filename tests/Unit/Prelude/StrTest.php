<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use Siler\Str;

class StrTest extends TestCase
{
    public function testSlugify()
    {
        $this->assertSame('foo-bar-baz', Str\slugify(' *&# FoO, Bar - Baz!!! '));
        $this->assertSame('foo_bar_baz', Str\slugify(' *&# FoO, Bar - Baz!!! ', ['separator' => '_']));
    }

    public function testLines()
    {
        $this->assertSame(['foo', 'bar', 'baz'], Str\lines("foo\nbar\nbaz"));
        $this->assertSame(['foo', 'bar', 'baz'], Str\lines("foo\r\nbar\r\nbaz"));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Str\starts_with('foo', 'foo'));
        $this->assertTrue(Str\starts_with('foo', 'fo'));
        $this->assertTrue(Str\starts_with('foo', 'f'));
        $this->assertFalse(Str\starts_with('foo', 'o'));
        $this->assertFalse(Str\starts_with('foo', 'of'));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str\ends_with('foo', 'o'));
        $this->assertTrue(Str\ends_with('foo', 'oo'));
        $this->assertTrue(Str\ends_with('foo', 'foo'));
        $this->assertFalse(Str\ends_with('foo', 'f'));
        $this->assertFalse(Str\ends_with('foo', 'fo'));
    }

    public function testContains()
    {
        $this->assertTrue(Str\contains('foo', 'f'));
        $this->assertTrue(Str\contains('foo', 'o'));
        $this->assertTrue(Str\contains('foo', 'fo'));
        $this->assertTrue(Str\contains('foo', 'oo'));

        $this->assertFalse(Str\contains('foo', 'a'));
    }

    public function testSnakeCase()
    {
        $this->assertSame('foo', Str\snake_case('foo'));
        $this->assertSame('foo', Str\snake_case('Foo'));
        $this->assertSame('foo_bar', Str\snake_case('fooBar'));
        $this->assertSame('foo_bar', Str\snake_case('FooBar'));
        $this->assertSame('foo_bar', Str\snake_case('FOOBar'));
        $this->assertSame('foo_bar', Str\snake_case('fooBAR'));
        $this->assertSame('foo_bar_baz', Str\snake_case('FooBarBaz'));
    }

    public function testCamelCase()
    {
        $this->assertSame('Foo', Str\camel_case('foo'));
        $this->assertSame('FooBar', Str\camel_case('foo_bar'));
        $this->assertSame('FooBarBaz', Str\camel_case('foo_bar_baz'));
    }
}
