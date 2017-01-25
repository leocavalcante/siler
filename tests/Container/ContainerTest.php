<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Container;

class ContainerTest extends TestCase
{
    public function testSet()
    {
        Container\set('test', 'test');
    }

    public function testGet()
    {
        $this->assertEquals('test', Container\get('test'));
    }
}
