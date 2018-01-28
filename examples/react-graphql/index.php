<?php

declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

use Siler\Diactoros;
use Siler\Graphql;
use Siler\Http;

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');
$resolvers = [
    'Query' => [
        'foo' => 'bar',
    ],
];

$schema = Graphql\schema($typeDefs, $resolvers);

echo "Server running at http://127.0.0.1:8080\n";
Http\server(Graphql\psr7($schema), function (\Throwable $err) {
    var_dump($err);

    return Diactoros\json([
        'error'   => true,
        'message' => $err->getMessage(),
    ]);
})()->run();
