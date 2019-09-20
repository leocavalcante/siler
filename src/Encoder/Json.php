<?php declare(strict_types=1);

namespace Siler\Encoder\Json;

/**
 * Sugar for JSON encoding. With defensive programming check.
 *
 * @param $value
 * @param int $options
 * @param int $depth
 *
 * @return string
 */
function encode($value, int $options = 0, int $depth = 512): string
{
    $json = \json_encode($value, $options, $depth);

    if ($json === false) {
        throw new \UnexpectedValueException('Could not encode the given value');
    }

    return $json;
}

/**
 * Sugar for JSON decoding. Defaults to associative array throw on error.
 *
 * @param $json
 * @param bool $assoc
 * @param int $options
 * @param int $depth
 *
 * @return mixed
 */
function decode($json, bool $assoc = true, int $options = JSON_THROW_ON_ERROR, int $depth = 512)
{
    return \json_decode($json, $assoc, $depth, $options);
}
