<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;

class ObjTest extends TestCase
{
    public function testPatch()
    {
        $target = new PatchFromToFixture();
        $target->foo = 'bar';

        $return_ref = $target->patch(['foo' => 'baz']);

        $this->assertSame($return_ref, $target);
        $this->assertSame('baz', $target->foo);
    }
}
