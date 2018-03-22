<?php declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use function Siler\Dotenv\env as env;

class DotenvTest extends TestCase
{
    public function testEnv()
    {
        $lines = \Siler\Dotenv\init(__DIR__.'/../../fixtures');

        $this->assertCount(4, $lines);
        $this->assertSame('FOO=bar', $lines[0]);
        $this->assertSame($_SERVER, env());
        $this->assertSame('bar', env('FOO'));
        $this->assertSame('baz', env('BAR', 'baz'));
        $this->assertNull(env('BAR'));
    }
}
