<?php

namespace Siler\Test\Integration;

use Siler\Twig;
use Siler\Route;
use Siler\Functional as F;

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

        Route\get('/', F\put(Twig\render('static.twig')));
    }
}
