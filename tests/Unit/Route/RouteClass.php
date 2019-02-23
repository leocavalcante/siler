<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Route;

use Siler\Route;

class RouteClass
{
    public function getIndex()
    {
        echo 'className.index';
    }

    public function postFoo()
    {
        echo 'className.postFoo';
    }

    public function putFooBar()
    {
        echo 'className.putFooBar';
        Route\stop_propagation();
    }

    public function anyIndex(string $baz, string $qux)
    {
        echo "className.$baz.$qux";
        Route\stop_propagation();
    }
}
