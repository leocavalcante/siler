<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Success;
use Siler\Result\Failure;
use function Siler\Result\failure;
use function Siler\Result\success;

class ResultTest extends TestCase
{
    public function testSuccess()
    {
        $success = success(0);
        $this->assertInstanceOf(Success::class, $success);
    }

    public function testFailure()
    {
        $failure = failure(0);
        $this->assertInstanceOf(Failure::class, $failure);
    }
}
