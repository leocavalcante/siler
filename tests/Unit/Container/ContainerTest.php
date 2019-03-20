<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Container;

class ContainerTest extends TestCase
{
    public function testSet()
    {
        Container\set('test', 'test');
        $this->assertContains('test', Container\Container::getInstance()->values);
    }

    public function testGet()
    {
        $this->assertSame('test', Container\get('test'));
    }

    public function testHas()
    {
        Container\Container::getInstance()->values['test_has'] = new \stdClass();
        $this->assertTrue(Container\has('test_has'));
        $this->assertFalse(Container\has('test_hasnt'));
    }

    public function testClear()
    {
        Container\Container::getInstance()->values['test_clear'] = new \stdClass();
        $this->assertTrue(Container\has('test_clear'));
        Container\clear('test_clear');
        $this->assertFalse(Container\has('test_clear'));
    }

}
