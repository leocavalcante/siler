<?php declare(strict_types=1);

namespace Siler\Result;

/**
 * @template T
 * @extends Result<T>
 */
final class Err extends Result
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
     * @return false
     */
    public function isOk(): bool
    {
        return false;
    }

    /**
     * @return true
     */
    public function isErr(): bool
    {
        return true;
    }
}
