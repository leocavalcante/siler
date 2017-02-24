<?php
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
function init($port = null)
{
    if (is_null($port)) {
        $port = 8080;
    }

    $messageComponent = new MessageComponent();
    $webSockerServer = new WsServer($messageComponent);
    $server = IoServer::factory(new HttpServer($webSockerServer), $port);

    Container\set(RATCHET_CONNECTIONS, new \SplObjectStorage());

    $server->run();
}

/**
 * Sets a callback for connected clients.
 *
 * @param \Closure $callback The callback function to call when there is a new client connected
 */
function connected($callback)
{
    Container\set(RATCHET_EVENT_OPEN, $callback);
}

/**
 * Sets a callback for incoming websocket messages.
 *
 * @param \Closure $callback The callback for incoming messages
 */
function inbox($callback)
{
    Container\set(RATCHET_EVENT_MESSAGE, $callback);
}

/**
 * Sets a callback for closed connections.
 *
 * @param \Closure $callback The callback for closed connections
 */
function closed($callback)
{
    Container\set(RATCHET_EVENT_CLOSE, $callback);
}

/**
 * Sets a callback to handle errors.
 *
 * @param \Closure $callback The callback for errors
 */
function error($callback)
{
    Container\set(RATCHET_EVENT_ERROR, $callback);
}

/**
 * Broadcast a message for the connected clients.
 *
 * @param string                   $message The message to broadcast
 * @param ConnectionInterface|null $from    The sender client, if given the message will not be broadcasted to it
 */
function broadcast($message, ConnectionInterface $from = null)
{
    $clients = Container\get(RATCHET_CONNECTIONS);

    foreach ($clients as $client) {
        if (!is_null($from) && $client === $from) {
            continue;
        }

        $client->send($message);
    }
}
