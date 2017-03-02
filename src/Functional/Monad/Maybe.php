<?php

namespace Siler\Functional\Monad;

class Maybe extends Identity
{
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
            return $this->value;
        }

        if (is_null($this->value)) {
            return new Maybe(null);
        }

        return new Maybe($function($this->value));
    }
}
