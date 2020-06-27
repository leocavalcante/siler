<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use Siler\Prelude\Collection;
use const Siler\Functional\even;
use const Siler\Functional\sum;

class CollectionTest extends TestCase
{
    public function testMap()
    {
        $collection = Collection::of([1, 2, 3]);
        $double = function (int $i): int {
            return $i * 2;
        };
        $this->assertSame([2, 4, 6], $collection->map($double)->unwrap());
    }

    public function testFilter()
    {
        $collection = Collection::of([1, 2, 3]);
        $this->assertSame([2], $collection->filter(even)->unwrap());
    }

    public function testFold()
    {
        $collection = Collection::of([1, 2, 3]);
        $this->assertSame(6, $collection->fold(0, sum));
    }
}
