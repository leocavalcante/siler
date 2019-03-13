<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use function Siler\Dotenv\env as env;

class DotenvTest extends TestCase
{
    public function testEnv()
    {
        $entries = \Siler\Dotenv\init(__DIR__ . '/../../fixtures');

        $this->assertCount(2, $entries);
        $this->assertArrayHasKey('FOO', $entries);
        $this->assertSame('bar', $entries['FOO']);
        $this->assertSame($_SERVER, env());
        $this->assertSame('bar', env('FOO'));
        $this->assertSame('baz', env('BAR', 'baz'));
        $this->assertNull(env('BAR'));
    }
}
