<?php

declare(strict_types=1);

namespace Siler\Functional\Monad;

class Maybe extends Identity
{
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
            return $this->return();
        }

        return $this->bind($function);
    }

    public function bind(callable $function)
    {
        if (is_null($this->value)) {
            return new self(null);
        }

        return new self($function($this->value));
    }

    public function return()
    {
        return $this->value;
    }
}
