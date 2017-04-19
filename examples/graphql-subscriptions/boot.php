<?php

use Siler\Graphql;
use RedBeanPHP\R;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

R::setup('sqlite:'.__DIR__.'/db.sqlite');

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');

$resolvers = [
    'Query' => [
        'messages' => function ($root, $args) {
            return R::findAll('message');
        }
    ],
    'Mutation' => [
        'addMessage' => function ($root, $args) {
            $message = R::dispense('message');
            $message->body = $args['body'];

            R::store($message);

            return $message;
        }
    ],
    'Subscription' => [
        'newMessage' => function ($root, $args) {
            return $root;
        }
    ],
];

Graphql\resolvers($resolvers);
return Graphql\schema($typeDefs);
