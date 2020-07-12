<?php declare(strict_types=1);

namespace Siler\Test\Unit\Result;

use PHPUnit\Framework\TestCase;
use Siler\Result\Err;
use Siler\Result\Ok;
use Siler\Result\Result;
use TypeError;
use function Siler\Result\err;
use function Siler\Result\ok;

class ResultTest extends TestCase
{
    public function testSuccess()
    {
        $success = ok();
        $this->assertInstanceOf(Ok::class, $success);
    }

    public function testFailure()
    {
        $failure = err();
        $this->assertInstanceOf(Err::class, $failure);
    }

    public function testMap()
    {
        $success = ok('foo')->map(function (string $val) {
            return ok("{$val}bar");
        });

        $this->assertInstanceOf(Ok::class, $success);
        $this->assertSame('foobar', $success->unwrap());

        $failure = err('baz')->map(function (string $baz) {
            return ok('qux');
        });

        $this->assertInstanceOf(Err::class, $failure);
        $this->assertSame('baz', $failure->unwrap());

        $successToFailure = ok(1)->map(function (int $n): Result {
            return err($n + 1);
        })->map(function (int $n): Result {
            return ok($n + 1);
        });

        $this->assertInstanceOf(Err::class, $successToFailure);
        $this->assertSame(2, $successToFailure->unwrap());
    }

    public function testMapThrows()
    {
        $this->expectException(TypeError::class);

        ok(1)->map(function (int $n): int {
            return $n;
        });
    }
}
