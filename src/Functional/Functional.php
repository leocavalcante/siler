<?php declare(strict_types=1);
/*
 * In computer science, functional programming is a programming paradigm
 * a style of building the structure and elements of computer programs
 * that treats computation as the evaluation of mathematical functions
 * and avoids changing-state and mutable data.
 */

namespace Siler\Functional;

use Closure;
use Traversable;

/**
 * Identity function.
 *
 * @template T
 * @return Closure(T): T
 */
function identity(): Closure
{
    return
        /**
         * @param mixed $value
         * @psalm-param T $value
         * @return mixed
         * @psalm-return T
         */
        static function ($value) {
            return $value;
        };
}

/**
 * Is a unary function which evaluates to $value for all inputs.
 *
 * @template T
 * @param mixed $value
 * @psalm-param T $value
 * @return Closure(): T
 */
function always($value): Closure
{
    return
        /**
         * @return mixed
         * @psalm-return T
         */
        static function () use ($value) {
            return $value;
        };
}

/**
 * Returns TRUE if $left is equal to $right and they are of the same type.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function equal($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left === $right;
        };
}

/**
 * Returns TRUE if $left is strictly less than $right.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function less_than($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left < $right;
        };
}

/**
 * Returns TRUE if $left is strictly greater than $right.
 *
 * @param mixed $right
 *
 * @return Closure(mixed): bool
 */
function greater_than($right): Closure
{
    return
        /**
         * @param mixed $left
         * @return bool
         */
        static function ($left) use ($right) {
            return $left > $right;
        };
}

/**
 * It allows for conditional execution of code fragments.
 *
 * @template I
 * @template O
 * @param callable(I):bool $cond
 * @return Closure(callable(I):O):((\Closure(callable(I):O):\Closure(I):O)
 */
function if_else(callable $cond): Closure
{
    return
        /**
         * @param callable(I):O $then
         * @return Closure(callable(I):O):(Closure(I):O)
         */
        static function (callable $then) use ($cond): Closure {
            return
                /**
                 * @param callable(I):O $else
                 * @return Closure(I):O
                 */
                static function (callable $else) use ($cond, $then): Closure {
                    return
                        /**
                         * @param mixed $value
                         * @psalm-param I $value
                         * @return mixed
                         * @psalm-return O
                         */
                        static function ($value) use ($cond, $then, $else) {
                            return $cond($value) ? $then($value) : $else($value);
                        };
                };
        };
}

/**
 * Pattern-Matching Semantics.
 *
 * @template I
 * @template O
 * @param array{callable(I):bool, callable(I):O}[] $matches
 * @param callable(I):O $exhaust
 * @return Closure(I):O
 */
function match(array $matches, callable $exhaust): Closure
{
    return
        /**
         * @param mixed $value
         * @psalm-param I $value
         * @return mixed
         * @psalm-return O
         */
        static function ($value) use ($matches, $exhaust) {
            foreach ($matches as [$predicate, $callback]) {
                if ($predicate($value)) {
                    return $callback($value);
                }
            }

            return $exhaust($value);
        };
}

/**
 * Determines whether any returns of $functions is true-ish.
 *
 * @param iterable<callable> $functions
 *
 * @return Closure(mixed): bool
 */
function any(iterable $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            foreach ($functions as $function) {
                if ($function($value)) {
                    return true;
                }
            }

            return false;
        };
}

/**
 * Determines whether all returns of $functions are true-ish.
 *
 * @param iterable<callable> $functions
 *
 * @return Closure(mixed): bool
 */
function all(iterable $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            foreach ($functions as $function) {
                if (!$function($value)) {
                    return false;
                }
            }

            return true;
        };
}

/**
 * Boolean "not".
 *
 * @param callable $function
 *
 * @return Closure(mixed): bool
 */
function not(callable $function): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($function): bool {
            return !$function($value);
        };
}

/**
 * Sum of $left and $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function add($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left + $right;
        };
}

/**
 * Product of $left and $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function mul($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left * $right;
        };
}

/**
 * Difference of $left and $right.
 *
 * @param int|float $right
 *
 * @return Closure(int|float): (int|float)
 */
function sub($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left - $right;
        };
}

/**
 * Quotient of $left and $right.
 *
 * @param int|float $right
 *
 * @return Closure(int|float): (int|float)
 */
function div($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left / $right;
        };
}

/**
 * Remainder of $left divided by $right.
 *
 * @param int|float $right
 * @return Closure(int|float): (int|float)
 */
function mod($right): Closure
{
    return
        /**
         * @param int|float $left
         * @return int|float
         */
        static function ($left) use ($right) {
            return $left % $right;
        };
}

/**
 * Function composition is the act of pipelining the result of one function,
 * to the input of another, creating an entirely new function.
 *
 * @param array<callable> $functions
 *
 * @return Closure(mixed): mixed
 */
function compose(array $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return mixed
         */
        static function ($value) use ($functions) {
            return array_reduce(
                array_reverse($functions),
                /**
                 * @param mixed $value
                 * @param callable $function
                 * @return mixed
                 */
                static function ($value, $function) {
                    return $function($value);
                },
                $value
            );
        };
}

/**
 * Converts the given $value to a boolean.
 *
 * @return Closure(mixed): bool
 */
function bool(): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value): bool {
            return (bool)$value;
        };
}

/**
 * In computer science, a NOP or NOOP (short for No Operation) is an assembly language instruction,
 * programming language statement, or computer protocol command that does nothing.
 *
 * @return Closure(): void
 */
function noop(): Closure
{
    return static function (): void {
    };
}

/**
 * Holds a function for lazily call.
 *
 * @param callable $function
 *
 * @return Closure(): mixed
 */
function hold(callable $function): Closure
{
    return
        /**
         * @return mixed
         */
        static function () use ($function) {
            return call_user_func_array($function, array_values(func_get_args()));
        };
}

/**
 * Lazy echo.
 *
 * @param string $value
 *
 * @return Closure(): void
 */
function puts($value): Closure
{
    return static function () use ($value): void {
        echo $value;
    };
}

/**
 * Flats a multi-dimensional array.
 *
 * @template T
 * @param mixed[] $list
 * @psalm-param list<T> $list
 * @return mixed[]
 * @psalm-return list<T>
 */
function flatten(array $list): array
{
    /** @psalm-var list<T> $flat */
    $flat = [];

    array_walk_recursive($list, /** @param mixed $value */ static function ($value) use (&$flat): void {
        /** @psalm-var T $value */
        $flat[] = $value;
    });

    /** @psalm-var list<T> */
    return $flat;
}

/**
 * Extract the first element of a list.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return mixed|null
 * @psalm-return T|null
 */
function head(array $list, $default = null)
{
    if (empty($list)) {
        return $default;
    }

    return array_shift($list);
}

/**
 * Extract the last element of a list.
 *
 * @param array $list
 * @param mixed $default
 *
 * @return mixed|null
 */
function last(array $list, $default = null)
{
    if (empty($list)) {
        return $default;
    }

    return array_pop($list);
}

/**
 * Extract the elements after the head of a list, which must be non-empty.
 *
 * @param array $list
 *
 * @return array
 */
function tail(array $list)
{
    return array_slice($list, 1);
}

/**
 * Return all the elements of a list except the last one. The list must be non-empty.
 *
 * @param array $list
 *
 * @return array
 */
function init(array $list): array
{
    return array_slice($list, 0, -1);
}

/**
 * Decompose a list into its head and tail.
 *
 * @param array $list
 * @return array{0: mixed, 1: array}
 */
function uncons(array $list): array
{
    return [$list[0], array_slice($list, 1)];
}

/**
 * Filter a list removing null values.
 *
 * @param array $list
 *
 * @return mixed[]
 */
function non_null(array $list): array
{
    return array_values(
        array_filter($list, function ($item) {
            return !is_null($item);
        })
    );
}

/**
 * Filter a list removing empty values.
 *
 * @param array $list
 * @return array
 */
function non_empty(array $list): array
{
    return array_values(
        array_filter($list, function ($item) {
            return !empty($item);
        })
    );
}

/**
 * Partial application.
 *
 * @param callable $callable
 * @param mixed ...$partial
 *
 * @return Closure(mixed[]): mixed
 */
function partial(callable $callable, ...$partial): Closure
{
    return
        /**
         * @param mixed[] $args
         * @return mixed
         */
        static function (...$args) use ($callable, $partial) {
            return call_user_func_array($callable, array_merge($partial, $args));
        };
}

/**
 * Calls a function if the predicate is true.
 *
 * @template T
 * @param callable $predicate
 * @return Closure(callable():T):(T|null)
 */
function if_then(callable $predicate): Closure
{
    return function (callable $then) use ($predicate) {
        if ($predicate()) {
            return $then();
        }

        return null;
    };
}

/**
 * A lazy empty evaluation.
 *
 * @param mixed $var
 *
 * @return Closure():bool
 */
function is_empty($var): Closure
{
    return static function () use ($var): bool {
        return empty($var);
    };
}

/**
 * A lazy is_null evaluation.
 *
 * @param mixed $var
 *
 * @return Closure():bool
 */
function isnull($var): Closure
{
    return static function () use ($var): bool {
        return is_null($var);
    };
}

/**
 * Returns a Closure that concatenates two strings using the given separator.
 *
 * @param string $separator
 *
 * @return Closure(string, string|false|null): string
 */
function concat(string $separator = ''): Closure
{
    return
        /**
         * @param string $a
         * @param string|false|null $b
         * @return string
         */
        static function (string $a, $b) use ($separator): string {
            if ($b === false || $b === null) {
                return $a;
            }

            return "{$a}{$separator}{$b}";
        };
}

/**
 * Lazily evaluate a function.
 *
 * @template T
 * @param callable(...mixed): T $callable
 * @param array<int, mixed> ...$args
 * @return Closure(): T
 */
function lazy(callable $callable, ...$args): Closure
{
    return
        /**
         * @return mixed
         * @psalm-return T
         */
        static function () use ($callable, $args) {
            return call($callable, ...$args);
        };
}

/**
 * A call_user_func alias.
 *
 * @template T
 * @param callable(...mixed): T $callable
 * @param array<int, mixed> ...$args
 * @return mixed
 * @psalm-return T
 */
function call(callable $callable, ...$args)
{
    /** @psalm-var T */
    return call_user_func_array($callable, $args);
}

/**
 * An universal array_map for any Traversable
 * and with a "fixed" argument order.
 *
 * @template I
 * @template O
 * @param Traversable|array $list
 * @psalm-param \Traversable<I>|I[] $list
 * @param callable(I, array-key):O $callback
 * @return mixed[]
 * @psalm-return O[]
 */
function map($list, callable $callback): array
{
    $agg = [];

    /**
     * @var array-key $key
     */
    foreach ($list as $key => $value) {
        $agg[$key] = $callback($value, $key);
    }

    return $agg;
}

/**
 * Lazy version of map.
 *
 * @template I
 * @template O
 * @param callable(I, array-key): O $callback
 * @return Closure(\Traversable<I>|I[]): O[]
 */
function lmap(callable $callback): Closure
{
    return
        /**
         * @param Traversable|array $list
         * @psalm-param \Traversable<I>|I[] $list
         * @return mixed[]
         * @psalm-return O[]
         */
        function ($list) use ($callback): array {
            return map($list, $callback);
        };
}

/**
 * Pipes functions calls.
 *
 * @param callable[] $callbacks
 * @return Closure
 */
function pipe(array $callbacks): Closure
{
    return
        /**
         * @param mixed|null $initial
         * @return mixed
         */
        static function ($initial = null) use ($callbacks) {
            return array_reduce(
                $callbacks,
                /**
                 * @param mixed $result
                 * @param callable $callback
                 * @return mixed
                 */
                static function ($result, callable $callback) {
                    return $callback($result);
                },
                $initial
            );
        };
}

/**
 * Pipes callbacks until null is reached,
 * it returns the last non-null value
 *
 * @param callable[] $callbacks
 * @return Closure
 */
function conduit(array $callbacks): Closure
{
    return
        /**
         * @param mixed|null $initial
         * @return mixed
         */
        static function ($initial = null) use ($callbacks) {
            /** @var mixed $value */
            $value = $initial;
            /** @var mixed $last */
            $last = $value;

            foreach ($callbacks as $callback) {
                /** @var mixed $value */
                $value = $callback($value);

                if ($value === null) {
                    return $last;
                }

                /** @var mixed $last */
                $last = $value;
            }

            return $last;
        };
}

/**
 * Returns a lazy version of concat.
 *
 * @param string $separator
 *
 * @return Closure(string|false|null):(Closure(string):string)
 */
function lconcat(string $separator = ''): Closure
{
    return
        /**
         * @param string|false|null $b
         * @return Closure(string): string
         */
        static function ($b) use ($separator): Closure {
            return static function (string $a) use ($separator, $b): string {
                return concat($separator)($a, $b);
            };
        };
}

/**
 * Lazy version of join().
 *
 * @param string $glue
 * @return Closure(array): string
 */
function ljoin(string $glue = ''): Closure
{
    return static function (array $pieces) use ($glue): string {
        return join($glue, $pieces);
    };
}

/**
 * An array_filter that dont preserve keys
 *
 * @template T
 * @param mixed[] $input
 * @psalm-param T[] $input
 * @param callable(T):bool $callback
 * @return mixed[]
 * @psalm-return T[]
 */
function filter(array $input, callable $callback): array
{
    return array_values(array_filter($input, $callback));
}

/**
 * Lazy version of filter.
 *
 * @template T
 * @param callable(T):bool $callback
 * @return Closure(T[]):T[]
 */
function lfilter(callable $callback): Closure
{
    return function (array $input) use ($callback): array {
        return filter($input, $callback);
    };
}

/**
 * Returns true if the given number is even.
 *
 * @param int $number
 * @return bool
 */
function even(int $number): bool
{
    return $number % 2 === 0;
}

/**
 * Returns true if the given number is odd.
 *
 * @param int $number
 * @return bool
 */
function odd(int $number): bool
{
    return !even($number);
}

/**
 * Returns the first element that matches the given predicate.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T):bool $predicate
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return mixed|null
 * @psalm-return T|null
 */
function find(array $list, callable $predicate, $default = null)
{
    foreach ($list as $item) {
        if ($predicate($item)) {
            return $item;
        }
    }

    return $default;
}

/**
 * Lazy version for find.
 *
 * @template T
 * @param callable(T):bool $predicate
 * @param mixed|null $default
 * @psalm-param T|null $default
 * @return Closure(T[]):(T|null)
 */
function lfind(callable $predicate, $default = null): Closure
{
    return function (array $list) use ($predicate, $default) {
        return find($list, $predicate, $default);
    };
}

/**
 * Sorts a list by a given compare/test function returning a new list without modifying the given one.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T, T):int $test
 * @return array
 * @psalm-return T[]
 */
function sort(array $list, callable $test): array
{
    usort($list, $test);
    return $list;
}

/**
 * Lazy version of the sort function.
 *
 * @template T
 * @param callable(T, T):int $test
 * @return Closure(T[]):T[]
 */
function lsort(callable $test): Closure
{
    return function (array $list) use ($test) {
        return sort($list, $test);
    };
}

/**
 * Returns the first element on a list after it is sorted. It is a head(sort()) alias.
 *
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param callable(T,T):int $test
 * @param mixed|null $if_empty
 * @psalm-param T|null $if_empty
 * @return mixed|null
 * @psalm-return T|null
 */
function first(array $list, callable $test, $if_empty = null)
{
    if (empty($list)) {
        return $if_empty;
    }

    return head(sort($list, $test));
}

/**
 * Lazy version of the `first` function.
 *
 * @template T
 * @param callable(T,T):int $test
 * @param mixed|null $if_empty
 * @psalm-param T|null $if_empty
 * @return Closure(T[]):(T|null)
 */
function lfirst(callable $test, $if_empty = null): Closure
{
    return function (array $list) use ($test, $if_empty) {
        return first($list, $test, $if_empty);
    };
}

/**
 * Sums two integers.
 *
 * @param int $a
 * @param int $b
 * @return int
 */
function sum(int $a, int $b): int
{
    return $a + $b;
}

/**
 * @template T
 * @param array $list
 * @psalm-param T[] $list
 * @param mixed $initial
 * @psalm-param T $initial
 * @param callable(T,T):T $callback
 * @return mixed
 * @psalm-return T
 */
function fold(array $list, $initial, callable $callback)
{
    $value = $initial;

    foreach ($list as $item) {
        $value = $callback($value, $item);
    }

    return $value;
}
