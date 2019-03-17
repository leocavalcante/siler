<?php

declare(strict_types=1);

namespace Siler\Result;

final class Success extends Result
{
    public function __construct($data = null, int $code = 0, string $id = null)
    {
        parent::__construct($data, $code, $id);
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
