<?php

use RedBeanPHP\R;
use Siler\Graphql;

R::setup('sqlite:'.__DIR__.'/db.sqlite');
Graphql\subscriptions_at('ws://127.0.0.1:8080');

$roomByName = function ($name) {
    return R::findOne('room', 'name = ?', [$name]);
};

$messages = function ($root, $args) use ($roomByName) {
    $roomName = $args['roomName'];
    $room = $roomByName($roomName);
    $messages = R::find('message', 'room_id = ?', [$room['id']]);

    return $messages;
};

$rooms = function () {
    return R::findAll('room');
};

$start = function ($root, $args) {
    $roomName = $args['roomName'];

    $room = R::dispense('room');
    $room['name'] = $roomName;

    R::store($room);

    return $room;
};

$chat = function ($root, $args) use ($roomByName) {
    $roomName = $args['roomName'];
    $body = $args['body'];

    $room = $roomByName($roomName);

    $message = R::dispense('message');
    $message['roomId'] = $room['id'];
    $message['body'] = $body;
    $message['timestamp'] = new \DateTime();

    R::store($message);

    return $message;
};

return [
    'Query' => compact('rooms', 'messages'),
    'Mutation' => compact('start', 'chat'),
    'Subscription' => [
        'inbox' => function ($root, $args) {
            return $root;
        },
    ],
];
