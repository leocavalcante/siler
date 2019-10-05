<?php declare(strict_types=1);

use Siler\GraphQL;
use Siler\Http\Request;
use Siler\Http\Response;
use Siler\Dotenv;

$dir = __DIR__;
$base_dir = dirname($dir, 2);
require_once "$base_dir/vendor/autoload.php";

Dotenv\init($dir);
GraphQL\subscriptions_at(getenv('SUBSCRIPTIONS_ENDPOINT'));

Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

if (Request\method_is('post')) {
    GraphQL\init(require("$dir/schema.php"));
}

