<?php

use Siler\Graphql;

$schema = require __DIR__.'/boot.php';

Graphql\subscriptions($schema)->run();
