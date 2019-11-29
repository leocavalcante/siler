<?php declare(strict_types=1);

namespace Siler\Swoole;

class RequestInterface
{
    /** @var array */
    public $header = [];
    /** @var array */
    public $server = [];
}
