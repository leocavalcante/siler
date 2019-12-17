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
 * @return Closure(mixed): mixed
 */
function identity(): Closure
{
    return
        /**
         * @param T $value
         * @return T
         */
        static function ($value) {
            return $value;
        };
}

/**
 * Is a unary function which evaluates to $value for all inputs.
 *
 * @template T
 * @param T $value
 * @return Closure(): mixed
 */
function always($value): Closure
{
    return
        /** @return T */
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
 * @param callable(mixed): bool $cond
 *
 * @return Closure(callable): Closure(callable): Closure(mixed): mixed
 */
function if_else(callable $cond): Closure
{
    return
        /**
         * @param callable(mixed): mixed $then
         * @return Closure(callable): Closure(mixed): mixed
         */
        static function (callable $then) use ($cond): Closure {
            return
                /**
                 * @param callable(mixed): mixed $else
                 * @return Closure(mixed): mixed
                 */
                static function (callable $else) use ($cond, $then): Closure {
                    return
                        /**
                         * @param mixed $value
                         * @return mixed
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
 * @param array $matches
 *
 * @return Closure
 *
 * @return Closure(mixed):mixed
 */
function match(array $matches): Closure
{
    return
        /**
         * @param mixed $value
         * @return mixed
         */
        static function ($value) use ($matches) {
            if (empty($matches)) {
                return null;
            }

            /** @var array<callable> $match */
            $match = $matches[0];
            $if_else = if_else($match[0])($match[1])(match(array_slice($matches, 1)));

            return $if_else($value);
        };
}

/**
 * Determines whether any returns of $functions is TRUE.
 *
 * @param array<callable> $functions
 *
 * @return Closure(mixed): bool
 */
function any(array $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            return array_reduce(
                $functions,
                /**
                 * @param mixed $current
                 * @param callable $function
                 * @return bool
                 */
                function ($current, $function) use ($value) {
                    return $current || $function($value);
                },
                false
            );
        };
}

/**
 * Determines whether all returns of $functions are TRUE.
 *
 * @param callable[] $functions
 *
 * @return Closure(mixed): bool
 */
function all(array $functions): Closure
{
    return
        /**
         * @param mixed $value
         * @return bool
         */
        static function ($value) use ($functions): bool {
            return array_reduce(
                $functions,
                /**
                 * @param mixed $current
                 * @param callable $function
                 * @return bool
                 */
                static function ($current, $function) use ($value) {
                    return $current && $function($value);
                },
                true
            );
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
         *
         * @return int|float
         * @return int
         */
        static function ($left) use ($right): int {
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
 * @param list<T> $list
 * @return list<T>
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
 * @param array $list
 * @param mixed $default
 *
 * @return mixed|null
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
 *
 * @return (array|mixed)[] [head, [tail]]
 *
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
 * @return array
 *
 * @return list<mixed>
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
 *
 * @return array
 *
 * @return list<mixed>
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
 * @param callable $predicate
 *
 * @return Closure
 *
 * @return Closure(callable):mixed
 */
function if_then(callable $predicate): Closure
{
    return function (callable $then) use ($predicate) {
        if ($predicate()) {
            $then();
        }
    };
}

/**
 * A lazy empty evaluation.
 *
 * @param mixed $var
 *
 * @return Closure
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
 * @return Closure
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
 * @param callable(): T $callable
 * @param array<int, mixed> ...$args
 * @return Closure(): T
 */
function lazy(callable $callable, ...$args): Closure
{
    return
        /**
         * @return T
         */
        function () use ($callable, $args) {
            return call($callable, ...$args);
        };
}

/**
 * A call_user_func alias.
 *
 * @template T
 * @param callable(): T $callable
 * @param array<int, mixed> ...$args
 * @return T
 */
function call(callable $callable, ...$args)
{
    /** @var T */
    return call_user_func_array($callable, $args);
}

/**
 * An universal array_map for any Traversable
 * and with a "fixed" argument order.
 *
 * @template I
 * @template O
 * @param Traversable<I> $list
 * @param callable(I, array-key): O $callback
 * @return O[]
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
 * @return Closure(\Traversable<I>): O[]
 */
function lmap(callable $callback): Closure
{
    return
        /**
         * @param Traversable<I> $list
         * @return O[]
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
 * @return Closure(string|false|null): Closure(string): string
 */
function lconcat(string $separator = ''): Closure
{
    return
        /**
         * @param string|false|null $b
         * @return Closure(string): string
         */
        static function ($b) use ($separator): Closure {
            return static function (string $a) use ($separator, $b) {
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
