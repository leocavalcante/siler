<?php

declare(strict_types=1);
/*
 * Helper functions to handle HTTP responses.
 */

namespace Siler\Http\Response;

use Siler\Http;


/**
 * Outputs the given parameters based on a HTTP response.
 *
 * @param string $content  The HTTP response body
 * @param int    $code     The HTTP response status code
 * @param string $mimeType A value for HTTP Header Content-Type
 * @param string $charset  The HTTP response charset
 *
 * @return int Returns 1, always
 */
function output(string $content = '', int $code = 204, string $mimeType = 'text/plain', string $charset = 'utf-8') : int
{
    http_response_code($code);
    \header(sprintf('Content-Type: %s;charset=%s', $mimeType, $charset));

    return print $content;
}


/**
 * Outputs a HTTP response as simple text.
 *
 * @param string $content The HTTP response body
 * @param int    $code    The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function text(string $content, int $code = 200, string $charset = 'utf-8') : int
{
    return output(strval($content), $code, 'text/plain', $charset);
}


/**
 * Outputs a HTML HTTP response.
 *
 * @param string $content The HTTP response body
 * @param int    $code    The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function html(string $content, int $code = 200, string $charset = 'utf-8') : int
{
    return output($content, $code, 'text/html', $charset);
}


/**
 * Outputs the given content as JSON mime type.
 *
 * @param string $content The HTTP response body
 * @param int    $code    The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function jsonstr(string $content, int $code = 200, string $charset = 'utf-8') : int
{
    return output(strval($content), $code, 'application/json', $charset);
}


/**
 * Outputs the given content encoded as JSON string.
 *
 * @param mixed  $content The HTTP response body
 * @param int    $code    The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function json($content, int $code = 200, string $charset = 'utf-8') : int
{
    $body = json_encode($content);

    if (false === $body) {
        throw new \UnexpectedValueException('Could not encode content');
    }

    return jsonstr($body, $code, $charset);
}


/**
 * Helper method to setup a header item as key-value parts.
 *
 * @param string $key     The response header name
 * @param string $val     The response header value
 * @param bool   $replace Should replace a previous similar header, or add a second header of the same type.
 */
function header(string $key, string $val, bool $replace = true)
{
    \header($key . ': ' . $val, $replace);
}


/**
 * Composes a default HTTP redirect response with the current base url.
 *
 * @param string $path
 */
function redirect(string $path)
{
    Http\redirect(Http\url($path));
}
