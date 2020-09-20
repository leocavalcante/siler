<?php declare(strict_types=1);

namespace Siler\Test\Unit\Config;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use function Siler\Config\{all, has, load, config};
use const Siler\Config\CONFIG;

final class ConfigTest extends TestCase
{
    private $config;

    public function setUp(): void
    {
        $this->config = load(__DIR__ . '/../../fixtures/config');
    }

    public function testLoad(): void
    {
        self::assertSame($this->config, Container\get(CONFIG));
    }

    public function testConfig(): void
    {
        self::assertSame('value', config('test.config'));
        self::assertSame('another_value', config('test.another_config'));
    }

    public function testConfigDefault(): void
    {
        self::assertNull(config('invalid_key'));
        self::assertSame(123, config('invalid.key', 123));
    }

    public function testHas(): void
    {
        self::assertTrue(has('test.config'));
        self::assertFalse(has('invalid.key'));
    }

    public function testAll(): void
    {
        self::assertSame([
            'test' => [
                'another_config' => 'another_value',
                'config' => 'value',
            ]
        ], all());
    }
}
