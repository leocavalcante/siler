<?php

namespace Siler\Functional\Monad;

class Identity
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
            return $this->value;
        }

        return new self($function($this->value));
    }
}
