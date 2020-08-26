<?php declare(strict_types=1);

namespace Siler\Functional\Monad;

/**
 * @template T
 */
class Identity
{
    /**
     * @var mixed
     * @psalm-var T
     */
    protected $value;

    /**
     * @param mixed $value
     * @psalm-param T $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param callable|null $function
     * @return self|mixed
     * @psalm-param callable(T):(T|null)|null $function
     * @psalm-return self|T
     */
    public function __invoke(callable $function = null)
    {
        if ($function === null) {
            return $this->value;
        }

        return new self($function($this->value));
    }

    /**
     * @return mixed
     * @psalm-return T
     */
    public function return()
    {
        return $this->value;
    }
}
