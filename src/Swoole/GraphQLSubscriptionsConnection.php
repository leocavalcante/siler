<?php declare(strict_types=1);

namespace Siler\Swoole;

use Siler\GraphQL\SubscriptionsConnection;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var Server */
    private $server;
    /** @var Frame */
    private $frame;

    public function __construct(Server $server, Frame $frame)
    {
        $this->server = $server;
        $this->frame = $frame;
    }

    public function send(string $data)
    {
        $this->server->send($this->frame->fd, $data);
    }
}
