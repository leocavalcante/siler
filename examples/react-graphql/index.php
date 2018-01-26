<?php

declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

use Siler\Graphql;
use Siler\Http;

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');
$resolvers = [
    'Query' => [
        'foo' => 'bar',
    ],
];

$schema = Graphql\schema($typeDefs, $resolvers);
Http\server(Graphql\psr7($schema))->run();
