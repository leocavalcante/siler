<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use OverflowException;
use PHPUnit\Framework\TestCase;
use Siler\Container;
use stdClass;
use UnderflowException;

class ContainerTest extends TestCase
{
    public function testSet()
    {
        Container\set('test', 'test');
        Container\set('lazy', static function () {
            return 'lazy';
        });
        $this->assertContains('test', Container\Container::getInstance()->values);
        $this->assertContains('lazy', Container\Container::getInstance()->values);
    }

    public function testGet()
    {
        $this->assertSame('test', Container\get('test'));
        $this->assertSame('lazy', Container\get('lazy'));
    }

    public function testHas()
    {
        Container\Container::getInstance()->values['test_has'] = new stdClass();
        $this->assertTrue(Container\has('test_has'));
        $this->assertFalse(Container\has('test_hasnt'));
    }

    public function testClear()
    {
        Container\Container::getInstance()->values['test_clear'] = new stdClass();
        $this->assertTrue(Container\has('test_clear'));
        Container\clear('test_clear');
        $this->assertFalse(Container\has('test_clear'));
    }

    public function testInjectOverflow()
    {
        Container\Container::getInstance()->values['test_inject_overflow'] = new stdClass();
        $this->expectException(OverflowException::class);
        Container\inject('test_inject_overflow', new stdClass());
    }

    public function testInject()
    {
        $service = new stdClass();
        Container\inject('test_inject', $service);
        $this->assertSame($service, Container\Container::getInstance()->values['test_inject']);
    }

    public function testRetrieveUnderflow()
    {
        $this->expectException(UnderflowException::class);
        Container\retrieve('test_retrieve_underflow');
    }

    public function testRetrieve()
    {
        $service = new stdClass();
        Container\Container::getInstance()->values['test_retrieve'] = $service;
        $this->assertSame($service, Container\retrieve('test_retrieve'));
        $this->assertSame('lazy', Container\retrieve('lazy'));
    }
}
