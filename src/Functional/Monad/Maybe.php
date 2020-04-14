<?php declare(strict_types=1);

namespace Siler\Functional\Monad;

/**
 * @template T
 * @extends Identity<T>
 */
class Maybe extends Identity
{
    /**
     * @param callable(T):(T|null)|null $function
     * @return self|mixed
     * @psalm-return self|T
     */
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
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
        if (is_null($this->value)) {
            return new self(null);
        }

        return new self($function($this->value));
    }
}
