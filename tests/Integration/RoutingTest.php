<?php

namespace Siler\Test\Integration;

use Siler\Functional as F;
use Siler\Route;
use Siler\Twig;

class RoutingTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $_SERVER['REQUEST_URI'] = '/';
    }

    public function testHelloWorld()
    {
        $this->expectOutputString('Hello World');
        Route\get('/', F\puts('Hello World'));
    }

    public function testStaticPages()
    {
        $this->expectOutputString('<p>Hello World</p>');

        Twig\init(__DIR__.'/../fixtures');

        Route\get('/', F\puts(Twig\render('static.twig')));
    }
}
