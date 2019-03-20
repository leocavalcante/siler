<?php

declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Dotenv as Env;
use Siler\SwiftMailer as Mail;
use function Siler\Dotenv\env;

Env\init(__DIR__);

$host = env('SMTP_HOST');
$port = intval(env('SMTP_PORT'));
$username = env('SMTP_USERNAME');
$password = env('SMTP_PASSWORD');
$from = env('FROM');
$to = env('TO');

// Setup a transport
$transport = Mail\smtp($host, $port, $username, $password);

// Setup a mailer
Mail\mailer($transport);

// Setup a message
$message = Mail\message('Siler + SwiftMailer', [$from], [$to], 'Siler rocks!');

// Send it!
Mail\send($message);
