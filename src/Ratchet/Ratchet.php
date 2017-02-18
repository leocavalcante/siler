<?php

namespace Siler\Ratchet;

use Siler\Container;

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\ConnectionInterface;

const RATCHET_CONNECTIONS = 'ratchet_connections';
const RATCHET_EVENT_OPEN = 'ratchet_event_open';
const RATCHET_EVENT_MESSAGE = 'ratchet_event_message';
const RATCHET_EVENT_CLOSE = 'ratchet_event_close';
const RATCHET_EVENT_ERROR = 'ratchet_event_error';

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

function connected($callback)
{
    Container\set(RATCHET_EVENT_OPEN, $callback);
}

function inbox($callback)
{
    Container\set(RATCHET_EVENT_MESSAGE, $callback);
}

function closed($callback)
{
    Container\set(RATCHET_EVENT_CLOSE, $callback);
}

function error($callback)
{
    Container\set(RATCHET_EVENT_ERROR, $callback);
}

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
