<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testEnum()
    {
        $foo = TestEnum::of(TestEnum::FOO);
        $this->assertSame(0, $foo->valueOf());

        $another_foo = TestEnum::of(TestEnum::FOO);
        $this->assertTrue($foo->sameAs($another_foo));

        $bar = TestEnum::of(TestEnum::BAR);
        $this->assertFalse($foo->sameAs($bar));

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Invalid value (foo) for enum (%s)', TestEnum::class));
        TestEnum::of('foo');
    }
}
