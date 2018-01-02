<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;

class SilerTest extends TestCase
{
    public function testArrayGet()
    {
        $fixture = ['foo' => 'bar'];
        $this->assertSame('bar', \Siler\array_get($fixture, 'foo'));
        $this->assertSame('qux', \Siler\array_get($fixture, 'baz', 'qux'));
    }

    public function testRequireFn()
    {
        $cb = \Siler\require_fn(__DIR__.'/../fixtures/foo.php');
        $this->assertSame('baz', $cb(['bar' => 'baz']));
    }
}
