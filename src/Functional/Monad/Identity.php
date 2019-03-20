<?php

declare(strict_types=1);

namespace Siler\Functional\Monad;

class Identity
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
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
