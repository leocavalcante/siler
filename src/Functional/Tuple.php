<?php

declare(strict_types=1);

namespace Siler\Functional;

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
class Tuple implements \ArrayAccess, \Countable
{
    private $values;

    /**
     * @internal Tuple constructor.
     *
     * @param $values
     */
    public function __construct($values)
    {
        $this->values = $values;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * @param mixed| $offset
     *
     * @throws \OutOfBoundsException
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        throw new \OutOfRangeException('Invalid tuple position');
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Tuples are immutable!');
    }

    /**
     * @param mixed| $offset
     *
     * @throws \RuntimeException
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Tuples are immutable!');
    }

    /**
     * @internal Count elements of the Tuple.
     *
     * @return int
     */
    public function count()
    {
        return count($this->values);
    }
}
