<?php

declare(strict_types=1);

namespace Siler\Result;

final class Success extends Result
{
    public function __construct(int $code, $data = null, string $id = null)
    {
        parent::__construct($code, $data, $id);
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function isFailure(): bool
    {
        return false;
    }
}
