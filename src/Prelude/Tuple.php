<?php

declare(strict_types=1);
/*
 * Tuple module.
 */

namespace Siler\Tuple;

use ArrayAccess;
use Countable;
use OutOfBoundsException;
use OutOfRangeException;
use RuntimeException;

/**
 * Creates a new Tuple.
 *
 * @param mixed ...$values
 *
 * @return Tuple
 */
function tuple(...$values)
{
    return new Tuple($values);
}

/**
 * A class representing a Tuple.
 */
final class Tuple implements ArrayAccess, Countable
{
    private $values;

    /**
     * @param array $values Tuple elements.
     * @internal Tuple constructor.
     *
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Returns Tuple values, useful for `list()`.
     *
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * @override
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * @override
     *
     * @param mixed $offset
     *
     * @return mixed
     * @throws OutOfBoundsException
     *
     */
    public function offsetGet($offset)
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        throw new OutOfRangeException('Invalid tuple position');
    }

    /**
     * @override
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws RuntimeException
     *
     * @suppress PhanUnusedPublicFinalMethodParameter
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Tuples are immutable!');
    }

    /**
     * @override
     *
     * @param mixed $offset
     *
     * @throws RuntimeException
     *
     * @suppress PhanUnusedPublicFinalMethodParameter
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Tuples are immutable!');
    }

    /**
     * @return int
     * @internal Count elements of the Tuple.
     *
     */
    public function count()
    {
        return count($this->values);
    }
}
