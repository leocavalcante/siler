<?php declare(strict_types=1);

namespace Siler\Result;

use JsonSerializable;

/**
 * @template T
 */
abstract class Result implements JsonSerializable
{
    /** @var T|null */
    private $data;
    /** @var int */
    private $code;
    /** @var string */
    private $id;

    /**
     * Result constructor.
     *
     * @param T|null $data
     * @param int $code
     * @param string|null $id
     */
    public function __construct($data = null, int $code = 0, string $id = null)
    {
        $this->id = is_null($id) ? base64_encode(uniqid()) : $id;
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    public function code(): int
    {
        return $this->code;
    }

    /**
     * @return T|null
     */
    public function unwrap()
    {
        return $this->data;
    }

    /**
     * @param callable(T|null): Result $fn
     * @return $this
     */
    public function bind(callable $fn): self
    {
        if ($this instanceof Success) {
            return $fn($this->unwrap());
        }

        return $this;
    }

    public function jsonSerialize()
    {
        $json = [
            'error' => $this->isFailure() && !$this->isSuccess(),
            'id' => $this->id
        ];

        if (is_null($this->data)) {
            return $json;
        }

        if (is_string($this->data)) {
            $json['message'] = $this->data;
            return $json;
        }

        if (is_array($this->data)) {
            return array_merge($json, $this->data);
        }

        $json['data'] = $this->data;
        return $json;
    }

    /**
     * @return bool
     */
    abstract public function isFailure(): bool;

    /**
     * @return bool
     */
    abstract public function isSuccess(): bool;
}

/**
 * Creates a new Success result monad.
 *
 * @template T
 *
 * @param T|null $data
 * @param int $code
 * @param string|null $id
 *
 * @return Success
 */
function success($data = null, int $code = 0, string $id = null): Success
{
    return new Success($data, $code, $id);
}

/**
 * Creates a new Failure result monad.
 *
 * @template T
 *
 * @param T|null $data
 * @param int $code
 * @param string|null $id
 *
 * @return Failure
 */
function failure($data = null, int $code = 1, string $id = null): Failure
{
    return new Failure($data, $code, $id);
}
