<?php declare(strict_types=1);

namespace Siler\Prelude;

use function Siler\Functional\{filter, fold, map};

/**
 * @template T
 */
final class Collection
{
    /**
     * @var array
     * @psalm-var T[]
     */
    private $list;

    /**
     * @param array $list
     * @psalm-param T[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @template I
     * @param iterable $iter
     * @psalm-param iterable<I> $iter
     * @return Collection
     * @psalm-return Collection<I>
     */
    public static function of(iterable $iter): Collection
    {
        $list = [];

        foreach ($iter as $item) {
            $list[] = $item;
        }

        return new Collection($list);
    }

    /**
     * @template I
     * @param callable(T):I $callback
     * @return Collection
     * @psalm-return Collection<I>
     */
    public function map(callable $callback): Collection
    {
        return new Collection(map($this->list, $callback));
    }

    /**
     * @param callable(T):bool $callback
     * @return Collection
     * @psalm-return Collection<T>
     */
    public function filter(callable $callback): Collection
    {
        return new Collection(filter($this->list, $callback));
    }

    /**
     * @param mixed $initial
     * @psalm-param T $initial
     * @param callable(T,T):T $callback
     * @return mixed
     * @psalm-return T
     */
    public function fold($initial, callable $callback)
    {
        return fold($this->list, $initial, $callback);
    }

    /**
     * Returns the internal value of the collection as array.
     *
     * @return array
     * @psalm-return T[]
     */
    public function toArray(): array
    {
        return $this->list;
    }

    /**
     * Checks if Collections are equals.
     *
     * @param Collection $other
     * @psalm-param Collection<T> $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->list === $other->list;
    }

    /**
     * Merges two Collections.
     *
     * @param Collection $other
     * @psalm-param Collection<T> $other
     * @return $this
     */
    public function merge(self $other): self
    {
        return new Collection(array_merge($this->list, $other->list));
    }

    /**
     * Implodes the Collection items into a string.
     *
     * @param string $glue
     * @return string
     */
    public function join(string $glue = ''): string
    {
        return implode($glue, $this->list);
    }

    /**
     * Returns the first item of the collection.
     *
     * @return mixed
     * @psalm-return T|null
     */
    public function first()
    {
        $key = array_key_first($this->list);
        return $key === null ? null : $this->list[$key];
    }

    /**
     * Returns the last item of the collection.
     *
     * @return mixed
     * @psalm-return T|null
     */
    public function last()
    {
        $key = array_key_last($this->list);
        return $key === null ? null : $this->list[$key];
    }

    /**
     * Checks if the collection has no elements.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->list);
    }
}
