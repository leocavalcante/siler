<?php

use Siler\Graphql;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$schema = include __DIR__.'/schema.php';
Graphql\subscriptions($schema, $filters)->run();
