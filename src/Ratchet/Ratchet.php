<?php declare(strict_types=1);

namespace Siler\Ratchet;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Siler\GraphQL\SubscriptionsManager;

/**
 * Creates and handles GraphQL subscriptions messages over Ratchet WebSockets.
 *
 * @param SubscriptionsManager $manager
 * @param int $port
 * @param string $host
 *
 * @return IoServer
 */
function graphql_subscriptions(
    SubscriptionsManager $manager,
    int $port = 3000,
    string $host = '0.0.0.0'
): IoServer
{
    $server = new GraphQLSubscriptionsServer($manager);
    $websocket = new WsServer($server);
    $http = new HttpServer($websocket);

    return IoServer::factory($http, $port, $host);
}
