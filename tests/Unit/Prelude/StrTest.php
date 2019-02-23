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
}
