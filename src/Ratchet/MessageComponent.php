<?php

declare(strict_types=1);
/**
 * Siler's internal MessageComponent.
 */

namespace Siler\Ratchet;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Siler\Container;

/**
 * @internal Not meant to be used
 */
class MessageComponent implements MessageComponentInterface
{
    /**
     * {@inheritdoc}.
     */
    public function onOpen(ConnectionInterface $conn)
    {
        Container\get(RATCHET_CONNECTIONS)->attach($conn);
        $this->callback(RATCHET_EVENT_OPEN, [$conn]);
    }

    /**
     * {@inheritdoc}.
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $this->callback(RATCHET_EVENT_MESSAGE, [$from, $message]);
    }

    /**
     * {@inheritdoc}.
     */
    public function onClose(ConnectionInterface $conn)
    {
        Container\get(RATCHET_CONNECTIONS)->detach($conn);
        $this->callback(RATCHET_EVENT_CLOSE, [$conn]);
    }

    /**
     * {@inheritdoc}.
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $this->callback(RATCHET_EVENT_ERROR, [$conn, $exception]);
        $conn->close();
    }

    /**
     * Helper function to call event callbacks checking for its existence.
     *
     * @param string $ratchetEvent The event name used in the Siler\Container
     * @param array  $params       The array of params to be used as arguments
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
