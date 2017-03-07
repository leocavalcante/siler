<?php

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
        $this->assertEquals('test', Container\get('test'));
    }
}
