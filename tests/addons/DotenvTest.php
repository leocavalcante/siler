<?php

use PHPUnit\Framework\TestCase;

class DotenvTest extends TestCase
{
    public function testEnv()
    {
        $lines = load_dotenv(__DIR__);

        $this->assertEquals(1, count($lines));
        $this->assertEquals('FOO=bar', $lines[0]);
        $this->assertEquals($_SERVER, env());
        $this->assertEquals('bar', env('FOO'));
        $this->assertEquals('baz', env('BAR', 'baz'));
        $this->assertNull(env('BAR'));
    }
}
