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
     * @param callable(T):(T|null)|null $function
     * @return self|mixed
     * @psalm-return self|T
     */
    public function __invoke(callable $function = null)
    {
        if (is_null($function)) {
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
