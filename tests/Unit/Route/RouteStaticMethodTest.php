<?php declare(strict_types=1);

namespace Siler\Test\Unit\Route;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteStaticMethodTest extends TestCase
{
    public function testStaticMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $result = Route\get('/', RouteClass::class . '::staticMethod');

        $this->assertSame('static_method', $result);
    }
}
