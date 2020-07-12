<?php /** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace Siler\Encoder\Json;

use function json_decode;
use function json_encode;

/**
 * Sugar for JSON encoding. With defensive programming check.
 *
 * @param mixed $value
 * @param int $options
 * @param int $depth
 * @return string
 */
function encode($value, int $options = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE, int $depth = 512): string
{
    return json_encode($value, $options, $depth);
}

/**
 * Sugar for JSON decoding. Defaults to associative array throw on error.
 *
 * @param string $json
 * @param bool $assoc
 * @param int $options
 * @param int $depth
 * @return array|string|int|float|object|bool
 */
function decode(string $json, bool $assoc = true, int $options = JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING, int $depth = 512)
{
    /** @var array|string|int|float|object|bool */
    return json_decode($json, $assoc, $depth, $options);
}
