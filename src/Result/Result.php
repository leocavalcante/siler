<?php

declare(strict_types=1);

namespace Siler\Result;

abstract class Result implements \JsonSerializable
{
    private $id;
    private $code;
    private $data;

    public function __construct(int $code, $data = null, string $id = null)
    {
        $this->id = is_null($id) ? base64_encode(uniqid()) : $id;
        $this->code = $code;
        $this->data = $data;
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
    abstract  public function isFailure(): bool;

    public function jsonSerialize()
    {
        $json = [
            'error' => $this->isFailure() && !$this->isSuccess(),
            'id' => $this->id,
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

function success(int $code, $data = null): Success
{
    return new Success($code, $data);
}

function failure(int $code, $data = null): Failure
{
    return new Failure($code, $data);
}
