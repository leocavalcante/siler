<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\GraphQL;
use Siler\Http\Request;
use Siler\Http\Response;

Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

GraphQL\subscriptions_at('ws://127.0.0.1:5000');

if (Request\method_is('post')) {
    $schema = include __DIR__ . '/schema.php';
    GraphQL\init($schema);
}
