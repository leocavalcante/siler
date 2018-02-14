<?php

namespace Siler\Test\Unit;

use Siler\Route;

class RouteFileWithPrefixTest extends \PHPUnit\Framework\TestCase
{
    public function testGetIndex()
    {
        $this->expectOutputString('index.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/';

        Route\files(__DIR__.'/../../fixtures/route_files/', '/foo');
    }

    public function testGetContact()
    {
        $this->expectOutputString('contact.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/contact';

        Route\files(__DIR__.'/../../fixtures/route_files/', '/foo');
    }

    public function testPostContact()
    {
        $this->expectOutputString('contact.post');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/foo/contact';

        Route\files(__DIR__.'/../../fixtures/route_files/', '/foo');
    }

    public function testGetAbout()
    {
        $this->expectOutputString('about.index.get');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/about';

        Route\files(__DIR__.'/../../fixtures/route_files/', '/foo');
    }

    public function testGetWithParam()
    {
        $this->expectOutputString('foo.$8.getfoo.@8.getfoo.8.get', '/foo');

        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/foo/8';

        Route\files(__DIR__.'/../../fixtures/route_files/', '/foo');
    }
}
