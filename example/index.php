<?php
use function Siler\Route\route;
require_once __DIR__.'/../vendor/autoload.php';
route('get', '/', 'pages/home.php');
