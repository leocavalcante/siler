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
     * @return array
     * @psalm-return T[]
     */
    public function unwrap(): array
    {
        return $this->list;
    }
}
