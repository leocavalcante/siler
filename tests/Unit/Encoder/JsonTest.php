<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Encoder;

use Exception;
use PHPUnit\Framework\TestCase;
use Siler\Encoder\Json;

class JsonTest extends TestCase
{
    public function testEncode()
    {
        $this->assertSame('{"foo":"bar","baz":8}', Json\encode(['foo' => 'bar', 'baz' => 8]));
    }

    public function testDecode()
    {
        $this->assertSame(['foo' => 'bar', 'baz' => 8], Json\decode('{"foo":"bar","baz":8}'));
    }

    public function testDecodeException()
    {
        // TODO: Use JsonException when PHP 7.2 support drops
        $this->expectException(Exception::class);
        Json\decode('invalid json');
    }

    public function testEncodeException()
    {
        // TODO: Use JsonException when PHP 7.2 support drops
        $this->expectException(Exception::class);
        Json\encode(fopen('php://input', 'r'));
    }
}
