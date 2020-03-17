<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use function Siler\Klass\unqualified_name;

class KlassTest extends TestCase
{
    public function testUnqualifiedName()
    {
        $this->assertSame('KlassTest', unqualified_name(KlassTest::class));
    }
}
