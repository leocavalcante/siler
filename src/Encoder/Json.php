<?php

declare(strict_types=1);

namespace Siler\Encoder\Json;

// TODO: Remove when PHP 7.2 support is dropped
if (!defined('JSON_THROW_ON_ERROR')) {
    define('JSON_THROW_ON_ERROR', 4194304);
}

/**
 * Sugar for JSON encoding. With defensive programming check.
 *
 * @param mixed $value
 * @param int $options
 * @param int $depth
 *
 * @return string
 * @throws \Exception
 */
function encode($value, int $options = JSON_THROW_ON_ERROR, int $depth = 512): string
{
    $json = \json_encode($value, $options, $depth);

    // TODO: Remove when PHP 7.2 support is dropped
    if ($json === false) {
        throw new \UnexpectedValueException('Could not encode the given value');
    }

    return $json;
}

/**
 * Sugar for JSON decoding. Defaults to associative array throw on error.
 *
 * @param string $json
 * @param bool $assoc
 * @param int $options
 * @param int $depth
 *
 * @return mixed
 * @throws \Exception
 */
function decode(string $json, bool $assoc = true, int $options = JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING, int $depth = 512)
{
    $value = \json_decode($json, $assoc, $depth, $options);

    // TODO: Remove when PHP 7.2 support is dropped
    if ($value === null) {
        throw new \UnexpectedValueException("Could not decode $json");
    }

    return $value;
}
