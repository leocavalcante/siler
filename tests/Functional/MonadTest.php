<?php

namespace Siler\Test;

use Siler\Functional as f;
use Siler\Functional\Monad;

class MonadTest extends \PHPUnit\Framework\TestCase
{
    public function testIdentity()
    {
        $monad = Monad\identity(1);
        $this->assertInstanceOf(Monad\Identity::class, $monad);
        $this->assertSame(1, $monad());

        $monad = $monad(f\add(1));
        $this->assertInstanceOf(Monad\Identity::class, $monad);
        $this->assertSame(2, $monad());
    }

    public function testMaybe()
    {
        $maybe = Monad\maybe(1);
        $this->assertInstanceOf(Monad\Maybe::class, $maybe);
        $this->assertSame(1, $maybe());

        $maybe = $maybe(f\add(1));
        $this->assertInstanceOf(Monad\Maybe::class, $maybe);
        $this->assertSame(2, $maybe());

        $maybe = $maybe(f\always(null));
        $this->assertInstanceOf(Monad\Maybe::class, $maybe);

        $maybe = $maybe(f\mul(2));
        $this->assertNull($maybe());
    }

    public function testMaybeTree()
    {
        $foo = ['name' => 'foo', 'parent' => null];
        $bar = ['name' => 'bar', 'parent' => $foo];
        $baz = ['name' => 'baz', 'parent' => $bar];

        $parent = function ($value) {
            return $value['parent'];
        };

        $grandparent = Monad\maybe($baz)($parent)($parent);
        $this->assertSame($foo, $grandparent());

        $grandparent = Monad\maybe($foo)($parent)($parent);
        $this->assertNull($grandparent());
    }
}
