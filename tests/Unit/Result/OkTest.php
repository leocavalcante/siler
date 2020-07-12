<?php declare(strict_types=1);

namespace Siler\Test\Unit\Result;

use PHPUnit\Framework\TestCase;
use Siler\Result\Ok;

class OkTest extends TestCase
{
    public function testConstructor()
    {
        $success = new Ok();
        $this->assertNull($success->unwrap());
    }

    public function testIsSuccess()
    {
        $success = new Ok();
        $this->assertTrue($success->isOk());
        $this->assertFalse($success->isErr());
    }
}
