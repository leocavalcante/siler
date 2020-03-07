<?php declare(strict_types=1);

namespace Siler\Example;

use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use Siler\GraphQL;
use function Siler\array_get_str;

R::setup('sqlite:' . __DIR__ . '/db.sqlite');

$room_by_name = function ($name): ?OODBBean {
    return R::findOne('room', 'name = ?', [$name]);
};

$room = [
    'messages' => function ($room) {
        return R::findAll('message', 'room_id = ?', [$room['id']]);
    }
];

$queries = [
    'rooms' => function () {
        return R::findAll('room');
    },
    'messages' => function ($root, $args) use ($room_by_name) {
        $room_name = array_get_str($args, 'roomName');
        $room = $room_by_name($room_name);
        return R::find('message', 'room_id = ?', [$room['id']]);
    }
];

$mutations = [
    'start' => function ($root, $args) {
        $room_name = array_get_str($args, 'roomName');

        $room = R::dispense('room');
        $room['name'] = $room_name;

        R::store($room);

        return $room;
    },
    'chat' => function ($root, $args) use ($room_by_name) {
        $room_name = array_get_str($args, 'roomName');
        $body = array_get_str($args, 'body');

        $room = $room_by_name($room_name);

        $message = R::dispense('message');
        $message['roomId'] = $room['id'];
        $message['body'] = $body;
        $message['timestamp'] = new \DateTime();

        R::store($message);

        $message['roomName'] = $room_name;
        // For the inbox filter
        GraphQL\publish('inbox', $message);
        // <- Exactly what "inbox" will receive
        return $message;
    },
    'close' => function ($root, array $args) use ($room_by_name): bool {
        $room_name = array_get_str($args, 'roomName');
        $room = $room_by_name($room_name);

        if ($room === null) {
            return false;
        }

        R::trash($room);
        return true;
    },
];

$subscriptions = [
    'inbox' => function ($message) {
        // <- Received from "publish"
        return $message;
    }
];

$directives = [
    '@upper' => function (callable $resolver): \Closure {
        return function ($root, $args, $context, $info) use ($resolver): string {
            $value = $resolver($root, $args, $context, $info);
            return mb_strtoupper($value, 'UTF-8');
        };
    },
];

return [
        'Room' => $room,
        'Query' => $queries,
        'Mutation' => $mutations,
        'Subscription' => $subscriptions,
    ] + $directives;
