<?php

declare(strict_types=1);

namespace Siler\Mistake;

/**
 * A serializable exception to speed up HTTP API development.
 *
 * @package Siler\Prelude
 */
class Mistake extends \Exception implements \JsonSerializable
{
    private $id;

    public function __construct(int $code, string $message)
    {
        parent::__construct($message, $code);
        $this->id = base64_encode(uniqid());
    }

    /**
     * Gets the generated ID for the Mistake.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'code' => $this->getCode(),
            'error' => true,
            'message' => $this->getMessage(),
        ];
    }
}

/**
 * Creates a Closure for a future Mistake.
 *
 * @param int $code
 * @param string $message
 *
 * @return \Closure
 */
function abort(int $code, string $message): \Closure
{
    return function () use ($code, $message) {
        throw new Mistake($code, $message);
    };
}
