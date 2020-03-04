<?php

declare(strict_types=1);

namespace Siler\Functional\Monad;

/**
 * Class Maybe
 * @package Siler\Functional\Monad
 */
class Maybe extends Identity
{
    /**
     * @param callable|null $function
     * @return $this|mixed|Identity
     */
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
            return $this->return();
        }

        return $this->bind($function);
    }

    /**
     * @param callable $function
     * @return $this
     */
    public function bind(callable $function): self
    {
        if (is_null($this->value)) {
            return new self(null);
        }

        return new self($function($this->value));
    }
}
