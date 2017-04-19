<?php

use Siler\Graphql;

$schema = require __DIR__.'/boot.php';

Graphql\init($schema);
