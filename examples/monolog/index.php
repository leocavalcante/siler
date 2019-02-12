<?php

declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Monolog as Log;

Log\handler(Log\stream(__DIR__ . '/siler.log'));

Log\debug('debug', ['level' => 'debug']);
Log\info('info', ['level' => 'info']);
Log\notice('notice', ['level' => 'notice']);
Log\warning('warning', ['level' => 'warning']);
Log\error('error', ['level' => 'error']);
Log\critical('critical', ['level' => 'critical']);
Log\alert('alert', ['level' => 'alert']);
Log\emergency('emergency', ['level' => 'emergency']);
