<?php

namespace Siler\Ws;

use Siler\Container;
use function Siler\array_get;

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
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
