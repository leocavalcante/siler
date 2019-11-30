<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Failure;
use Siler\Result\Result;
use Siler\Result\Success;

use function Siler\Result\failure;
use function Siler\Result\success;

class ResultTest extends TestCase
{
    public function testSuccess()
    {
        $success = success();
        $this->assertInstanceOf(Success::class, $success);
    }

    public function testFailure()
    {
        $failure = failure();
        $this->assertInstanceOf(Failure::class, $failure);
    }

    public function testBind()
    {
        $success = success('foo')->bind(function (string $val) {
            return success("{$val}bar");
        });

        $this->assertInstanceOf(Success::class, $success);
        $this->assertSame('foobar', $success->unwrap());

        $failure = failure('baz')->bind(function (string $baz) {
            return success('qux');
        });

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertSame('baz', $failure->unwrap());

        $successToFailure = success(1)->bind(function (int $n): Result {
            return failure($n + 1);
        })->bind(function (int $n): Result {
            return success($n + 1);
        });

        $this->assertInstanceOf(Failure::class, $successToFailure);
        $this->assertSame(2, $successToFailure->unwrap());
    }

    public function testBindThrows()
    {
        $this->expectException(\TypeError::class);

        success(1)->bind(function (int $n): int {
            return $n;
        });
    }
}
