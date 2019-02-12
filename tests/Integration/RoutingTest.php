<?php

declare(strict_types=1);

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

        Twig\init(__DIR__ . '/../fixtures');

        Route\get('/', F\puts(Twig\render('static.twig')));
    }


    public function testDynamicPages()
    {
        $this->expectOutputString('<p>hello-world</p>');

        Twig\init(__DIR__ . '/../fixtures');

        $_SERVER['REQUEST_URI'] = '/hello-world';

        Route\get(
            '/{foo}',
            function ($params) {
                echo Twig\render('template.twig', $params);
            }
        );
    }


    public function testFiles()
    {
        $this->expectOutputString('index.get');

        $_SERVER['REQUEST_URI'] = '/';

        Route\files('tests/fixtures/route_files/');
    }
}//end class
