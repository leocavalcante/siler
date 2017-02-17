<?php

namespace Siler\Ratchet;

use Siler\Container;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessageComponent implements MessageComponentInterface
{
    const RATCHET_CONNECTIONS = 'ratchet_connections';

    const RATCHET_EVENT_OPEN = 'ratchet_event_open';
    const RATCHET_EVENT_MESSAGE = 'ratchet_event_message';
    const RATCHET_EVENT_CLOSE = 'ratchet_event_close';
    const RATCHET_EVENT_ERROR = 'ratchet_event_error';

    public function onOpen(ConnectionInterface $conn)
    {
        Container\get(self::RATCHET_CONNECTIONS)->attach($conn);
        $this->callback(self::RATCHET_EVENT_OPEN, [$conn]);
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        $this->callback(self::RATCHET_EVENT_MESSAGE, [$from, $message]);
    }

    public function onClose(ConnectionInterface $conn)
    {
        Container\get(self::RATCHET_CONNECTIONS)->detach($conn);
        $this->callback(self::RATCHET_EVENT_CLOSE, [$conn]);
    }

    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $this->callback(self::RATCHET_EVENT_ERROR, [$conn, $exception]);
        $conn->close();
    }

    private function callback($ratchetEvent, $params)
    {
        $callback = Container\get($ratchetEvent);

        if (is_null($callback)) {
            return;
        }

        if (!is_callable($callback)) {
            return;
        }

        call_user_func_array($callback, $params);
    }
}
