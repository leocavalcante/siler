<?php

use Siler\Graphql;

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');
$resolvers = include __DIR__.'/resolvers.php';

Graphql\resolvers($resolvers);

return Graphql\schema($typeDefs);
