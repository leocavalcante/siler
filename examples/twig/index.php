<?php

use Siler\{Twig, Route, Http\Response};

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Twig\init('examples/twig/');

Route\get('/{name}', function ($params) {
    Response\html(Twig\render('home.twig', $params));
});
