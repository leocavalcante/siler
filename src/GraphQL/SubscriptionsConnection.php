<?php

declare(strict_types=1);

namespace Siler\GraphQL;

/**
 * Interface SubscriptionsConnection
 * @package Siler\GraphQL
 */
interface SubscriptionsConnection
{
    /**
     * @param string $data
     */
    public function send(string $data): void;
    /** @return int|string */
    public function key();
}
