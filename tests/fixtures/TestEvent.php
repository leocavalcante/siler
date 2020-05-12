<?php declare(strict_types=1);

namespace Siler\Test\fixtures;

/**
 * Class TestEvent
 * @package Siler\Test\fixtures
 */
final class TestEvent
{
    /** @var string */
    public $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }
}
