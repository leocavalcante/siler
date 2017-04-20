<?php

use Siler\Graphql;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$schema = include __DIR__.'/schema.php';
Graphql\subscriptions($schema)->run();
