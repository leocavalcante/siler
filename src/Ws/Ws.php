<?php

namespace Siler\Ws;

use Siler\Container;

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\ConnectionInterface;

function init($port = null)
{
    if (is_null($port)) {
        $port = 8080;
    }

    $messageComponent = new MessageComponent();
    $webSockerServer = new WsServer($messageComponent);
    $server = IoServer::factory(new HttpServer($webSockerServer), $port);

    Container\set('ws_clients', new \SplObjectStorage());

    $server->run();
}

function on($event, $callback)
{
    Container\set('ws_on_'.$event, $callback);
}

function onopen($callback)
{
    on('open', $callback);
}

function onmessage($callback)
{
    on('message', $callback);
}

function onclose($callback)
{
    on('close', $callback);
}

function onerror($callback)
{
    on('error', $callback);
}

function broadcast($message, ConnectionInterface $from = null)
{
    $clients = Container\get('ws_clients');

    foreach ($clients as $client) {
        if (!is_null($from) && $client === $from) {
            continue;
        }

        $client->send($message);
    }
}
