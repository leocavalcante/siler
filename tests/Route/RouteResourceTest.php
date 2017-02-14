<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Route;

class RouteResouceTest extends TestCase
{
    public function testIndexResource()
    {
        $this->expectOutputString('resources.index');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/resources';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreateResource()
    {
        $this->expectOutputString('resources.create');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/resources/create';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');

        ob_end_clean(); // test only purposes
    }

    public function testStoreResource()
    {
        $this->expectOutputString('resources.store bar');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/resources';
        $_POST['foo'] = 'bar';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    public function testShowResource()
    {
        $this->expectOutputString('resources.show 8');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/resources/8';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    public function testEditResource()
    {
        $this->expectOutputString('resources.edit 8');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/resources/8/edit';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    public function testUpdateResource()
    {
        $this->expectOutputString('resources.update 8');

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['PATH_INFO'] = '/resources/8';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    public function testDesotryResource()
    {
        $this->expectOutputString('resources.destroy 8');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['PATH_INFO'] = '/resources/8';

        Route\resource('/resources', __DIR__.'/../fixtures/resources');
    }

    public function testOverrideIdentity()
    {
        $this->expectOutputString('resources.edit foo');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/resources/foo/edit';

        Route\resource('/resources', __DIR__.'/../fixtures/resources/slug', 'slug');
    }
}
