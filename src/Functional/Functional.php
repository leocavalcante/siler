<?php

namespace Siler\Functional;

function identity()
{
    return function ($value) {
        return $value;
    };
}

function always($value)
{
    return function () use ($value) {
        return $value;
    };
}

function equal($right)
{
    return function ($left) use ($right) {
        return $left === $right;
    };
}

function less_than($right)
{
    return function ($left) use ($right) {
        return $left < $right;
    };
}

function greater_than($right)
{
    return function ($left) use ($right) {
        return $left > $right;
    };
}

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

function any(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($current, $function) use ($value) {
            return $current || $function($value);
        }, false);
    };
}

function all(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($current, $function) use ($value) {
            return $current && $function($value);
        }, true);
    };
}

function not(callable $function)
{
    return function ($value) use ($function) {
        return !$function($value);
    };
}

function add($right)
{
    return function ($left) use ($right) {
        return $left + $right;
    };
}

function mul($right)
{
    return function ($left) use ($right) {
        return $left * $right;
    };
}

function sub($right)
{
    return function ($left) use ($right) {
        return $left - $right;
    };
}

function div($right)
{
    return function ($left) use ($right) {
        return $left / $right;
    };
}

function compose(array $functions)
{
    return function ($value) use ($functions) {
        return array_reduce($functions, function ($value, $function) {
            return $function($value);
        }, $value);
    };
}
