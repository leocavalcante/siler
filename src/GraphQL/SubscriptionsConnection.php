<?php

declare(strict_types=1);

namespace Siler\GraphQL;

interface SubscriptionsConnection
{
    public function send(string $data);
    public function key();
}
