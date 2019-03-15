<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Result\Success;

class SuccessTest extends TestCase
{
    public function testConstructor()
    {
        $success = new Success();
        $this->assertNotEmpty($success->id());
        $this->assertNull($success->unwrap());
        $this->assertSame(0, $success->code());
    }

    public function testIsSuccess()
    {
        $success = new Success();
        $this->assertTrue($success->isSuccess());
        $this->assertFalse($success->isFailure());
    }

    public function testJson()
    {
        $success = new Success(null, 0, 'test');
        $this->assertSame('{"error":false,"id":"test"}', json_encode($success));

        $success = new Success('foo', 0, 'test');
        $this->assertSame('{"error":false,"id":"test","message":"foo"}', json_encode($success));

        $success = new Success(['foo' => 'bar'], 0, 'test');
        $this->assertSame('{"error":false,"id":"test","foo":"bar"}', json_encode($success));

        $success = new Success(true, 0, 'test');
        $this->assertSame('{"error":false,"id":"test","data":true}', json_encode($success));
    }
}
