<?php declare(strict_types=1);

namespace Siler\Functional\Monad;

/**
 * @template T
 * @extends Identity<T>
 */
class Maybe extends Identity
{
    /**
     * @param callable|null $function
     * @return self|mixed
     * @psalm-param callable(T):(T|null)|null $function
     * @psalm-return self|T
     */
    public function __invoke(callable $function = null)
    {
        if ($function === null) {
            return $this->return();
        }

        return $this->bind($function);
    }

    /**
     * @param callable(T):(T|null) $function
     * @return self
     */
    public function bind(callable $function): self
    {
        if ($this->value === null) {
            return new self(null);
        }

        return new self($function($this->value));
    }
}
