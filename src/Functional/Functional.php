<?php
/**
 * In computer science, functional programming is a programming paradigm
 * a style of building the structure and elements of computer programs
 * that treats computation as the evaluation of mathematical functions
 * and avoids changing-state and mutable data.
 */

namespace Siler\Functional;

/**
 * Identity function.
 *
 * @return callable $value -> $value
 */
function identity()
{
    return function ($value) {
        return $value;
    };
}

/**
 * Is a unary function which evaluates to $value for all inputs.
 *
 * @param mixed $value
 *
 * @return callable a -> $value
 */
function always($value)
{
    return function () use ($value) {
        return $value;
    };
}

/**
 * Returns TRUE if $left is equal to $right and they are of the same type.
 *
 * @param mixed $right
 *
 * @return callable $left -> bool
 */
function equal($right)
{
    return function ($left) use ($right) {
        return $left === $right;
    };
}

/**
 * Returns TRUE if $left is strictly less than $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> bool
 */
function less_than($right)
{
    return function ($left) use ($right) {
        return $left < $right;
    };
}

/**
 * Returns TRUE if $left is strictly greater than $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> bool
 */
function greater_than($right)
{
    return function ($left) use ($right) {
        return $left > $right;
    };
}

/**
 * It allows for conditional execution of code fragments.
 *
 * @param callable $pred
 *
 * @return callable $then -> $else -> $value -> mixed
 */
function if_else(callable $pred)
{
    return function (callable $then) use ($pred) {
        return function (callable $else) use ($pred, $then) {
            return function ($value) use ($pred, $then, $else) {
                return $pred($value) ? $then($value) : $else($value);
            };
        };
    };
}

/**
 * Pattern-Matching Semantics.
 *
 * @param array $matches
 *
 * @return callable $value -> mixed|null
 */
function match(array $matches)
{
    return function ($value) use ($matches) {
        if (empty($matches)) {
            return null;
        }

        $match = $matches[0];

        return if_else($match[0])($match[1])(match(array_slice($matches, 1)))($value);
    };
}

/**
 * Determines whether any returns of $functions is TRUE.
 *
 * @param array $functions
 *
 * @return callable $value -> bool
 */
function any(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($current, $function) use ($value) {
            return $current || $function($value);
        }, false);
    };
}

/**
 * Determines whether all returns of $functions are TRUE.
 *
 * @param array $functions
 *
 * @return callable $value -> bool
 */
function all(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($current, $function) use ($value) {
            return $current && $function($value);
        }, true);
    };
}

/**
 * Boolean "not".
 *
 * @param callable $function
 *
 * @return callable $value -> ! $function $value
 */
function not(callable $function)
{
    return function ($value) use ($function) {
        return !$function($value);
    };
}

/**
 * Sum of $left and $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> $left + $right
 */
function add($right)
{
    return function ($left) use ($right) {
        return $left + $right;
    };
}

/**
 * Product of $left and $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> $left * $right
 */
function mul($right)
{
    return function ($left) use ($right) {
        return $left * $right;
    };
}

/**
 * Difference of $left and $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> $left - $right
 */
function sub($right)
{
    return function ($left) use ($right) {
        return $left - $right;
    };
}

/**
 * Quotient of $left and $right.
 *
 * @param mixed $right
 *
 * @return callable $left -> $left / $right
 */
function div($right)
{
    return function ($left) use ($right) {
        return $left / $right;
    };
}

/**
 * Function composition is the act of pipelining the result of one function,
 * to the input of another, creating an entirely new function.
 *
 * @param array $functions
 *
 * @return callable $value -> mixed
 */
function compose(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($value, $function) {
            return $function($value);
        }, $value);
    };
}

/**
 * Converts the given $value to a boolean.
 *
 * @return callable $value -> bool
 */
function bool()
{
    return function ($value) {
        return (bool) $value;
    };
}

/**
 * In computer science, a NOP or NOOP (short for No Operation) is an assembly language instruction,
 * programming language statement, or computer protocol command that does nothing.
 *
 * @return callable a ->
 */
function noop()
{
    return function () {
    };
}

/**
 * Holds a function for lazily call.
 *
 * @param callable $function [description]
 *
 * @return callable a -> $function(a)
 */
function hold(callable $function)
{
    return function () use ($function) {
        return call_user_func_array($function, func_get_args());
    };
}
