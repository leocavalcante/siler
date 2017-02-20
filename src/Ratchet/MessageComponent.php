<?php
/**
 * Siler's internal MessageComponent
 */

namespace Siler\Ratchet;

use Siler\Container;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * @internal Not meant to be used
 */
class MessageComponent implements MessageComponentInterface
{
    /**
     * {@inherit}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        Container\get(RATCHET_CONNECTIONS)->attach($conn);
        $this->callback(RATCHET_EVENT_OPEN, [$conn]);
    }

    /**
     * {@inherit}
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $this->callback(RATCHET_EVENT_MESSAGE, [$from, $message]);
    }

    /**
     * {@inherit}
     */
    public function onClose(ConnectionInterface $conn)
    {
        Container\get(RATCHET_CONNECTIONS)->detach($conn);
        $this->callback(RATCHET_EVENT_CLOSE, [$conn]);
    }

    /**
     * {@inherit}
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $this->callback(RATCHET_EVENT_ERROR, [$conn, $exception]);
        $conn->close();
    }

    /**
     * Helper function to call event callbacks checking for its existence
     *
     * @param  string $ratchetEvent The event name used in the Siler\Container
     * @param  array $params The array of params to be used as arguments
     */
    private function callback($ratchetEvent, array $params)
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
