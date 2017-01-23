<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;

class SilerTest extends TestCase
{
    public function testArrayGet()
    {
        $fixture = ['foo' => 'bar'];
        $this->assertEquals('bar', \Siler\array_get($fixture, 'foo'));
        $this->assertEquals('qux', \Siler\array_get($fixture, 'baz', 'qux'));
    }

    public function testRequireFn()
    {
        $cb = \Siler\require_fn(__DIR__.'/fixtures/foo.php');
        $this->assertEquals('baz', $cb(['bar' => 'baz']));
    }
}
