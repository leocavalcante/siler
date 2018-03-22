<?php

declare(strict_types=1);

/**
 * Helper functions to work with Ratchet.
 */

namespace Siler\Ratchet;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Siler\Container;

const RATCHET_CONNECTIONS = 'ratchet_connections';
const RATCHET_EVENT_OPEN = 'ratchet_event_open';
const RATCHET_EVENT_MESSAGE = 'ratchet_event_message';
const RATCHET_EVENT_CLOSE = 'ratchet_event_close';
const RATCHET_EVENT_ERROR = 'ratchet_event_error';

/**
 * Initialize the Ratchet server. Note: this blocks the reset of code execution.
 *
 * @param int $port The port number on which the server should run. Defaults to 8080
 */
function init(int $port = 8080) : IoServer
{
    $messageComponent = new MessageComponent();
    $webSocketServer = new WsServer($messageComponent);
    $server = IoServer::factory(new HttpServer($webSocketServer), $port);

    Container\set(RATCHET_CONNECTIONS, new \SplObjectStorage());

    return $server;
}

/**
 * Sets a callback for connected clients.
 *
 * @param callable $callback The callback function to call when there is a new client connected
 */
function connected(callable $callback)
{
    Container\set(RATCHET_EVENT_OPEN, $callback);
}

/**
 * Sets a callback for incoming web socket messages.
 *
 * @param callable $callback The callback for incoming messages
 */
function inbox(callable $callback)
{
    Container\set(RATCHET_EVENT_MESSAGE, $callback);
}

/**
 * Sets a callback for closed connections.
 *
 * @param callable $callback The callback for closed connections
 */
function closed(callable $callback)
{
    Container\set(RATCHET_EVENT_CLOSE, $callback);
}

/**
 * Sets a callback to handle errors.
 *
 * @param callable $callback The callback for errors
 */
function error(callable $callback)
{
    Container\set(RATCHET_EVENT_ERROR, $callback);
}

/**
 * Broadcast a message for the connected clients.
 *
 * @param string                   $message The message to broadcast
 * @param ConnectionInterface|null $from    The sender client. If given, the message will not be broadcast to it
 */
function broadcast(string $message, ConnectionInterface $from = null)
{
    $clients = Container\get(RATCHET_CONNECTIONS);

    /** @var ConnectionInterface $client */
    foreach ($clients as $client) {
        if (!is_null($from) && $client === $from) {
            continue;
        }

        $client->send($message);
    }
}
