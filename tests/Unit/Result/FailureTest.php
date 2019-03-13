<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Failure;

class FailureTest extends TestCase
{
    public function testConstructor()
    {
        $failure = new Failure(42);
        $this->assertNotEmpty($failure->getId());
        $this->assertSame(42, $failure->getCode());
        $this->assertNull($failure->getData());
    }

    public function testIsSuccess()
    {
        $failure = new Failure(42);
        $this->assertTrue($failure->isFailure());
        $this->assertFalse($failure->isSuccess());
    }

    public function testJson()
    {
        $failure = new Failure(42, null, 'test');
        $this->assertSame('{"error":true,"id":"test"}', json_encode($failure));

        $failure = new Failure(43, 'foo', 'test');
        $this->assertSame('{"error":true,"id":"test","message":"foo"}', json_encode($failure));

        $failure = new Failure(43, ['foo' => 'bar'], 'test');
        $this->assertSame('{"error":true,"id":"test","foo":"bar"}', json_encode($failure));

        $failure = new Failure(43, true, 'test');
        $this->assertSame('{"error":true,"id":"test","data":true}', json_encode($failure));
    }
}
