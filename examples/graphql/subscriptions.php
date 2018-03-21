<?php

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

use Siler\Graphql;

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$host = '0.0.0.0';
$port = 5000;
$schema = include __DIR__.'/schema.php';

printf("Listening at %s:%s\n", $host, $port);

Graphql\ws($schema, $filters, $host, $port)->run();
