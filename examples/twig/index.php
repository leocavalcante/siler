<?php

use Siler\Http\Response;
use Siler\Route;
use Siler\Twig;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Twig\init('examples/twig/');

Route\get(
    '/{name}',
    function ($params) {
        Response\html(Twig\render('home.twig', $params));
    }
);
