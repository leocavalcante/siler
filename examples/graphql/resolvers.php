<?php

use RedBeanPHP\R;
use Siler\Graphql;

R::setup('sqlite:'.__DIR__.'/db.sqlite');

$roomByName = function ($name) {
    return R::findOne('room', 'name = ?', [$name]);
};

$roomType = [
    'messages' => function ($room) {
        return R::findAll('message', 'room_id = ?', [$room['id']]);
    },
];

$queryType = [
    'rooms' => function () {
        return R::findAll('room');
    },
    'messages' => function () use ($roomByName) {
        $roomName = $args['roomName'];
        $room = $roomByName($roomName);
        $messages = R::find('message', 'room_id = ?', [$room['id']]);

        return $messages;
    },
];

$mutationType = [
    'start' => function ($root, $args) {
        $roomName = $args['roomName'];

        $room = R::dispense('room');
        $room['name'] = $roomName;

        R::store($room);

        return $room;
    },
    'chat' => function ($root, $args) use ($roomByName) {
        $roomName = $args['roomName'];
        $body = $args['body'];

        $room = $roomByName($roomName);

        $message = R::dispense('message');
        $message['roomId'] = $room['id'];
        $message['body'] = $body;
        $message['timestamp'] = new \DateTime();

        R::store($message);

        $message['roomName'] = $roomName; // For the inbox filter
        Graphql\publish('inbox', $message); // <- Exactly what "inbox" will receive

        return $message;
    },
];

$subscriptionType = [
    'inbox' => function ($message) { // <- Received from "publish"
        return $message;
    },
];

return [
    'Room'         => $roomType,
    'Query'        => $queryType,
    'Mutation'     => $mutationType,
    'Subscription' => $subscriptionType,
];
