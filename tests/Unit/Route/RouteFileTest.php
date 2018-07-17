<?php

declare(strict_types=1);

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

    public function testGetWithParam()
    {
        $this->expectOutputString('foo.8.getfoo.@8.getfoo.$8.get');

        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/8';

        Route\files(__DIR__.'/../../fixtures/route_files/');
    }

    public function testNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);
        Route\files('path/does/not/exists');
    }
}
