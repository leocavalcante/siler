<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use Siler\Prelude\Collection;
use PHPUnit\Framework\TestCase;
use function Siler\Functional\mul;
use function Siler\Prelude\collect;
use const Siler\Functional\even;
use const Siler\Functional\sum;

final class CollectionTest extends TestCase
{

    public function testFilter(): void
    {
        self::assertTrue(collect([1, 2, 3, 4])->filter(even)->equals(collect([2, 4])));
    }

    public function testToArray(): void
    {
        self::assertSame([1, 2, 3], collect([1, 2, 3])->toArray());
    }

    public function testFold(): void
    {
        self::assertSame(6, collect([1, 2, 3])->fold(0, sum));
    }

    public function testMap(): void
    {
        self::assertTrue(collect([1, 2, 3])->map(mul(2))->equals(collect([2, 4, 6])));
    }

    public function testMerge(): void
    {
        self::assertTrue(collect([1, 2, 3, 4, 5, 6])->equals(collect([1, 2, 3])->merge(collect([4, 5, 6]))));
    }

    public function testJoin(): void
    {
        self::assertSame('123', collect([1, 2, 3])->join());
        self::assertSame('1,2,3', collect([1, 2, 3])->join(','));
    }

    public function testFirst(): void
    {
        self::assertSame(1, collect([1, 2 ,3])->first());
        self::assertNull(collect([])->first());
    }

    public function testLast(): void
    {
        self::assertSame(3, collect([1, 2 ,3])->last());
        self::assertNull(collect([])->last());
    }

    public function testIsEmpty(): void
    {
        self::assertTrue(collect([])->isEmpty());
        self::assertFalse(collect([1, 2, 3])->isEmpty());
    }
}
