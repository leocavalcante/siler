<?php

declare(strict_types=1);

namespace Siler\Functional\Monad;

/**
 * @template T
 */
class Identity
{
    /** @psalm-var T */
    protected $value;

    /** @psalm-param T $value */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param callable|null $function
     * @psalm-return self|T
     * @return mixed
     */
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
            return $this->value;
        }

        return new self($function($this->value));
    }

    /**
     * @psalm-return T
     */
    public function return()
    {
        return $this->value;
    }
}
