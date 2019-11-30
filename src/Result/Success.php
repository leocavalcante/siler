<?php

declare(strict_types=1);

namespace Siler\Result;

/**
 * @template T
 * @extends Result<T>
 */
final class Success extends Result
{
    /**
     * @param T|null $data
     * @param int $code
     * @param string|null $id
     */
    public function __construct($data = null, int $code = 0, string $id = null)
    {
        parent::__construct($data, $code, $id);
    }

    /**
     * @return true
     */
    public function isSuccess(): bool
    {
        return true;
    }

    /**
     * @return false
     */
    public function isFailure(): bool
    {
        return false;
    }
}
