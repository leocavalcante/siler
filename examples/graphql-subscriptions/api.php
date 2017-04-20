<?php

use Siler\Graphql;
use Siler\Http\Request;
use Siler\Http\Response;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

if (Request\method_is('post')) {
    $schema = include __DIR__.'/schema.php';
    Graphql\init($schema);
}
