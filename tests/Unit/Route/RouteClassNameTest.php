<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteClassNameTest extends TestCase
{
    public function testIndex()
    {
        $this->expectOutputString('className.index');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/class-name';
        Route\class_name('/class-name', 'Siler\Test\Unit\Route\RouteClass');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testPostFoo()
    {
        $this->expectOutputString('className.postFoo');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/class-name/foo';
        Route\class_name('/class-name', 'Siler\Test\Unit\Route\RouteClass');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testPutFooBar()
    {
        $this->expectOutputString('className.putFooBar');
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['REQUEST_URI'] = '/class-name/foo/bar';
        Route\class_name('/class-name', 'Siler\Test\Unit\Route\RouteClass');
    }

    public function testAnyParams()
    {
        $this->expectOutputString('className.baz.qux');
        $_SERVER['REQUEST_METHOD'] = 'ANYTHING';
        $_SERVER['REQUEST_URI'] = '/class-name/baz/qux';
        Route\class_name('/class-name', 'Siler\Test\Unit\Route\RouteClass');
    }

    public function tearDown(): void
    {
        Route\resume();
    }

    protected function setUp(): void
    {
        Route\purge_match();
    }
}
