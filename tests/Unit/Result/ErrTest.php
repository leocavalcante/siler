<?php declare(strict_types=1);

namespace Siler\Test\Unit\Result;

use PHPUnit\Framework\TestCase;
use Siler\Result\Err;

class ErrTest extends TestCase
{
    public function testConstructor()
    {
        $failure = new Err();
        $this->assertNull($failure->unwrap());
    }

    public function testIsFailure()
    {
        $failure = new Err();
        $this->assertTrue($failure->isErr());
        $this->assertFalse($failure->isOk());
    }
}
