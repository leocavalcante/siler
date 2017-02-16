<?php

namespace Siler\Ws;

use Siler\Container;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessageComponent implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        Container\get('ws_clients')->attach($conn);
        $this->callback('open', [$conn]);
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        $this->callback('message', [$from, $message]);
    }

    public function onClose(ConnectionInterface $conn)
    {
        Container\get('ws_clients')->detach($conn);
        $this->callback('close', [$conn]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->callback('error', [$conn, $e]);
        $conn->close();
    }

    private function callback($name, $params)
    {
        $callback = Container\get('ws_on_'.$name);

        if (is_null($callback)) {
            return;
        }

        if (!is_callable($callback)) {
            return;
        }

        call_user_func_array($callback, $params);
    }
}
