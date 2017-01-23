<?php

use function Siler\require_fn as rfn;
use function Siler\Http\route;

require __DIR__.'/../vendor/autoload.php';

route('/^\/$/', rfn('pages/home.php'));
