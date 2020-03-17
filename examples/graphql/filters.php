<?php declare(strict_types=1);

namespace Siler\Example\GraphQL;

return [
    'inbox' => function (array $payload, array $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];
