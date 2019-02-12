<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Http\Request;
use Siler\Route;

Route\any(
    '/any-route',
    function () {
        if (Request\method_is('get')) {
            echo 'On any route with GET method';
        }

        if (Request\method_is('post')) {
            echo 'On any route with POST method';
        }

        if (Request\method_is('patch')) {
            echo 'On any route with PATCH method';
        }
    }
);
