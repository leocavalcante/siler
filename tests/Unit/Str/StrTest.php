<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Str;

use PHPUnit\Framework\TestCase;
use Siler\Str;

class StrTest extends TestCase
{
    public function testSlugify()
    {
        $this->assertSame('foo-bar-baz', Str\slugify(' *&# FoO, Bar - Baz!!! '));
        $this->assertSame('foo_bar_baz', Str\slugify(' *&# FoO, Bar - Baz!!! ', ['separator' => '_']));
    }
}

