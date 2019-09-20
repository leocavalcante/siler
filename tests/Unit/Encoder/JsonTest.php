<?php declare(strict_types=1);

namespace Siler\Test\Unit\Encoder;

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
        $this->expectException(\JsonException::class);
        Json\decode('invalid json');
    }

    public function testEncodeException()
    {
        $this->expectException(\UnexpectedValueException::class);
        Json\encode(fopen('php://input', 'r'));
    }
}
