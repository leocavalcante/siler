<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

use function Siler\Dotenv\env as env;
use function Siler\Dotenv\bool_val;
use function Siler\Dotenv\int_val;
use function Siler\Dotenv\requires;
use function Siler\Dotenv\init;

class DotenvTest extends TestCase
{
    public function testEnv()
    {
        $entries = init(__DIR__ . '/../../fixtures');

        $this->assertCount(11, $entries);
        $this->assertArrayHasKey('FOO', $entries);
        $this->assertSame('bar', $entries['FOO']);
        $this->assertSame($_SERVER, env());
        $this->assertSame('bar', env('FOO'));
        $this->assertSame('baz', env('BAR', 'baz'));
        $this->assertNull(env('BAR'));
    }

    public function testEvnInt()
    {
        init(__DIR__ . '/../../fixtures');

        $this->assertSame(8, int_val('ENV_INT'));
        $this->assertNull(int_val('ENV_INT_NULL'));
        $this->assertSame(0, int_val('ENV_INT_DEFAULT', 0));
        $this->assertNull(int_val('ENV_INT_NOT_NUMERIC'));
    }

    public function testEnvBool()
    {
        init(__DIR__ . '/../../fixtures');

        $this->assertNull(bool_val('ENV_BOOL_NULL'));

        foreach (range(0, 5) as $index) {
            $this->assertFalse(bool_val("ENV_BOOL_FV$index"), "$index isn't falsy");
        }

        $this->assertTrue(bool_val('ENV_BOOL_TV'));
    }

    public function testEnvRequires()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('BAR is not set in the environment variables');

        init(__DIR__ . '/../../fixtures');

        requires('FOO');
        requires('BAR');
    }
}
