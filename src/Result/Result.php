<?php

declare(strict_types=1);

namespace Siler\Result;

use JsonSerializable;

/**
 * @template <T>
 */
abstract class Result implements JsonSerializable
{
    /** @var mixed */
    private $data;
    /** @var int */
    private $code;
    /** @var string */
    private $id;

    public function __construct($data = null, int $code = 0, string $id = null)
    {
        $this->id = is_null($id) ? base64_encode(uniqid()) : $id;
        $this->data = $data;
        $this->code = $code;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(): int
    {
        return $this->code;
    }

    /**
     * @return mixed|null
     */
    public function unwrap()
    {
        return $this->data;
    }

    public function bind(callable $fn): self
    {
        if ($this instanceof Success) {
            $val = $fn($this->unwrap());

            if (!($val instanceof Result)) {
                throw new \InvalidArgumentException('$fn argument at Result->bind should return a Result');
            }

            return $val;
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

    abstract public function isFailure(): bool;

    abstract public function isSuccess(): bool;
}

function success($data = null, int $code = 0, string $id = null): Success
{
    return new Success($data, $code, $id);
}

function failure($data = null, int $code = 1, string $id = null): Failure
{
    return new Failure($data, $code, $id);
}
