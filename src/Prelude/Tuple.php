<?php /** @noinspection PhpIllegalPsrClassPathInspection */
declare(strict_types=1);
/*
 * Tuple module.
 */

namespace Siler\Prelude;

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
    /** @var array */
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
     * @override
     *
     * @param string|int $offset
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
     * @param string|int $offset
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
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Tuples are immutable!');
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }
}
