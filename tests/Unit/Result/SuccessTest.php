<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Success;

class SuccessTest extends TestCase
{
    public function testConstructor()
    {
        $success = new Success(42);
        $this->assertNotEmpty($success->getId());
        $this->assertSame(42, $success->getCode());
        $this->assertNull($success->getData());
    }

    public function testIsSuccess()
    {
        $success = new Success(42);
        $this->assertTrue($success->isSuccess());
        $this->assertFalse($success->isFailure());
    }

    public function testJson()
    {
        $success = new Success(42, null, 'test');
        $this->assertSame('{"error":false,"id":"test"}', json_encode($success));

        $success = new Success(43, 'foo', 'test');
        $this->assertSame('{"error":false,"id":"test","message":"foo"}', json_encode($success));

        $success = new Success(43, ['foo' => 'bar'], 'test');
        $this->assertSame('{"error":false,"id":"test","foo":"bar"}', json_encode($success));

        $success = new Success(43, true, 'test');
        $this->assertSame('{"error":false,"id":"test","data":true}', json_encode($success));
    }
}
