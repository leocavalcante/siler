<?php

use RedBeanPHP\R;
use Siler\Graphql;

R::setup('sqlite:'.__DIR__.'/db.sqlite');
Graphql\subscriptions_at('ws://127.0.0.1:8080');

return [
    'Query' => [
        'messages' => function ($root, $args) {
            return R::findAll('message');
        },
    ],
    'Mutation' => [
        'addMessage' => function ($root, $args) {
            $message = R::dispense('message');
            $message->body = $args['body'];

            R::store($message);

            Graphql\publish('newMessage', $message);

            return $message;
        },
    ],
    'Subscription' => [
        'newMessage' => function ($root, $args) {
            return $root;
        },
    ],
];
