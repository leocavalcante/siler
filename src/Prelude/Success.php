<?php

declare(strict_types=1);

namespace Siler\Success;

/**
 * Sugar to tell that there is no errors.
 *
 * @package Siler\Success
 */
class Success implements \JsonSerializable
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return array_merge(['error' => false], $this->data);
    }
}

/**
 * Lazy create a Success.
 *
 * @param array $data
 *
 * @return \Closure
 */
function success(array $data = []): \Closure
{
    return function () use ($data): Success {
        return new Success($data);
    };
}
