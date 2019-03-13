<?php

declare(strict_types=1);

namespace Siler\Result;

final class Failure extends Result
{
    public function __construct(int $code, $data = null, string $id = null)
    {
        parent::__construct($code, $data, $id);
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isFailure(): bool
    {
        return true;
    }
}
