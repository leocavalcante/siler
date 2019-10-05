<?php

declare(strict_types=1);

namespace Siler\Swoole;

use Siler\GraphQL\SubscriptionsConnection;

class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var int */
    private $fd;

    public function __construct(int $fd)
    {
        $this->fd = $fd;
    }

    public function send(string $data)
    {
        push($data, $this->fd);
    }

    public function key()
    {
        return $this->fd;
    }
}
