<?php

use Siler\Graphql;
use Siler\Http\Response;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

$typeDefs = file_get_contents('examples/graphql/schema.graphql');

$resolvers = [
    'Query' => [
        'message' => 'foo',
    ],
    'Mutation' => [
        'sum' => function ($root, $args) {
            return $args['a'] + $args['b'];
        },
    ],
];

Graphql\init(Graphql\schema($typeDefs, $resolvers));
