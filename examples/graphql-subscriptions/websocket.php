<?php

use Siler\Graphql;

require __DIR__.'/boot.php';

$schema = include __DIR__.'/schema.php';
Graphql\subscriptions($schema)->run();
