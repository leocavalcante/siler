<?php

use function Siler\route as route;
use function Siler\require_fn as rfn;

require __DIR__.'/../vendor/autoload.php';

route('/^\/$/', rfn('pages/home.php'));
