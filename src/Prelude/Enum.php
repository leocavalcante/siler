<?php declare(strict_types=1);

namespace Siler\Prelude;

/**
 * Abstract class for enums.
 */
abstract class Enum
{
    /** @var array<string, array> */
    private static $constsMemo = [];

    /**
     * @param mixed $value
     * @return static
     * @throws \ReflectionException
     */
    public static function of($value): self
    {
        return new static($value);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected static function consts(): array
    {
        $class_name = static::class;

        if (!array_key_exists($class_name, static::$constsMemo)) {
            $reflection = new \ReflectionClass($class_name);
            static::$constsMemo[$class_name] = $reflection->getConstants();
        }

        return static::$constsMemo[$class_name];
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws \ReflectionException
     */
    protected static function valid($value): bool
    {
        return in_array($value, array_values(static::consts()), true);
    }

    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     * @throws \ReflectionException
     */
    public function __construct($value)
    {
        if (!static::valid($value)) {
            $class_name = static::class;
            throw new \UnexpectedValueException("Invalid value ($value) for enum ($class_name)");
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function valueOf()
    {
        return $this->value;
    }

    /**
     * @param Enum $other
     * @return bool
     */
    public function sameAs(Enum $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
