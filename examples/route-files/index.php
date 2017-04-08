<?php

use RedBeanPHP\R;
use Siler\Route;
use Siler\Twig;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

R::setup('sqlite:examples/route-files/.models');
Twig\init('examples/route-files/views/');
Route\files('examples/route-files/controllers/');
