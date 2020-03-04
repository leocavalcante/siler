<?php

declare(strict_types=1);

namespace Siler\Swoole;

use Siler\GraphQL\SubscriptionsConnection;

/**
 * Class GraphQLSubscriptionsConnection
 * @package Siler\Swoole
 */

/**
 * Class GraphQLSubscriptionsConnection
 * @package Siler\Swoole
 */
class GraphQLSubscriptionsConnection implements SubscriptionsConnection
{
    /** @var int */
    private $fd;

    /**
     * GraphQLSubscriptionsConnection constructor.
     * @param int $fd
     */
    /**
     * GraphQLSubscriptionsConnection constructor.
     * @param int $fd
     */
    public function __construct(int $fd)
    {
        $this->fd = $fd;
    }

    /**
     * @param string $data
     */
    public function send(string $data): void
    {
        push($data, $this->fd);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->fd;
    }
}
