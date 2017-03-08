<?php

namespace Siler\Test\Unit;

use Siler\Route;

class RouteFileTest extends \PHPUnit\Framework\TestCase
{
    public function testGetIndex()
    {
        $this->expectOutputString('index.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        Route\files(__DIR__.'/../../fixtures/route_files/');
    }

    public function testGetContact()
    {
        $this->expectOutputString('contact.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/contact';

        Route\files(__DIR__.'/../../fixtures/route_files/');
    }

    public function testPostContact()
    {
        $this->expectOutputString('contact.post');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/contact';

        Route\files(__DIR__.'/../../fixtures/route_files/');
    }

    public function testGetAbout()
    {
        $this->expectOutputString('about.index.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/about';

        Route\files(__DIR__.'/../../fixtures/route_files/');
    }
}
