<?php declare(strict_types=1);

namespace Siler\Result;

/**
 * @template T
 * @extends Result<T>
 */
final class Ok extends Result
{
    /**
     * @param mixed|null $data
     * @psalm-param T|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    /**
     * @return true
     */
    public function isOk(): bool
    {
        return true;
    }

    /**
     * @return false
     */
    public function isErr(): bool
    {
        return false;
    }
}
