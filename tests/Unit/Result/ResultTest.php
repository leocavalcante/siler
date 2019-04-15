<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Failure;
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
}
