<?php

declare(strict_types=1);

namespace Siler\Swoole;

use Siler\GraphQL\SubscriptionsConnection;
use Swoole\WebSocket\Frame;

class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var Frame */
    private $frame;

    public function __construct(Frame $frame)
    {
        $this->frame = $frame;
    }

    public function send(string $data)
    {
        push($data, $this->frame->fd);
    }

    public function key()
    {
        return $this->frame->fd;
    }
}
