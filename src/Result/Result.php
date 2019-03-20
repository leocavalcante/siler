<?php

declare(strict_types=1);

namespace Siler\Result;

abstract class Result implements \JsonSerializable
{
    private $id;
    private $data;
    private $code;

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

    public function unwrap()
    {
        return $this->data;
    }

    abstract public function isSuccess(): bool;
    abstract public function isFailure(): bool;

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
}

function success($data = null, int $code = 0, string $id = null): Success
{
    return new Success($data, $code, $id);
}

function failure($data = null, int $code = 1, string $id = null): Failure
{
    return new Failure($data, $code, $id);
}
