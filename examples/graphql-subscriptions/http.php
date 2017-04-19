<?php

use Siler\Graphql;
use Siler\Http\Request;
use Siler\Http\Response;

require __DIR__.'/boot.php';

Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

if (Request\method_is('options')) {
    exit();
}

$schema = include __DIR__.'/schema.php';
Graphql\init($schema);
