<?php

namespace Siler\Test\Integration;

use Siler\Functional as F;
use Siler\Route;
use Siler\Twig;

class RoutingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testStaticPages()
    {
        $this->expectOutputString('<p>Hello World</p>');

        $_SERVER['REQUEST_URI'] = '/';

        Twig\init(__DIR__.'/../fixtures');

        Route\get('/', F\puts(Twig\render('static.twig')));
    }
}
