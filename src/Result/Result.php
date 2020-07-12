<?php declare(strict_types=1);

namespace Siler\Result;

/**
 * @template T
 */
abstract class Result
{
    /**
     * @var mixed
     * @psalm-var T|null
     */
    private $data;

    /**
     * Result constructor.
     *
     * @psalm-param T|null $data
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @psalm-return T|null
     */
    public function unwrap()
    {
        return $this->data;
    }

    /**
     * @param callable(T|null): Result $fn
     * @return Result
     */
    public function map(callable $fn): self
    {
        if ($this instanceof Ok) {
            return $fn($this->unwrap());
        }

        return $this;
    }

    /**
     * @return bool
     */
    abstract public function isErr(): bool;

    /**
     * @return bool
     */
    abstract public function isOk(): bool;
}

/**
 * Creates a new Ok result monad.
 *
 * @template T
 * @param mixed|null $data
 * @return Ok
 * @psalm-param T|null $data
 */
function ok($data = null): Ok
{
    return new Ok($data);
}

/**
 * Creates a new Err result monad.
 *
 * @template T
 * @param mixed|null $data
 * @psalm-param T|null $data
 * @return Err
 */
function err($data = null): Err
{
    return new Err($data);
}
