<?php
/**
 * Helper functions to handle HTTP responses
 */

namespace Siler\Http\Response;

/**
 * Outputs the given parameters based on a HTTP response
 *
 * @param string $content The HTTP response body
 * @param int $code The HTTP response status code
 * @param string $mimeType A value for HTTP Header Content-Type
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function output($content = '', $code = 204, $mimeType = 'text/plain', $charset = 'utf-8')
{
    http_response_code($code);
    \header(sprintf('Content-Type: %s;charset=%s', $mimeType, $charset));
    return print($content);
}

/**
 * Outputs a HTTP response as simple text
 *
 * @param string $content The HTTP response body
 * @param int $code The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function text($content, $code = 200, $charset = 'utf-8')
{
    return output(strval($content), $code, 'text/plain', $charset);
}

/**
 * Outputs a HTTP response as entitized-HTML
 *
 * @param string $content The HTTP response body
 * @param int $code The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function html($content, $code = 200, $charset = 'utf-8')
{
    return output(htmlentities($content), $code, 'text/html', $charset);
}

/**
 * Outputs the given content as JSON Mimetype
 *
 * @param string $content The HTTP response body
 * @param int $code The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function jsonstr($content, $code = 200, $charset = 'utf-8')
{
    return output(strval($content), $code, 'application/json', $charset);
}

/**
 * Outputs the given content encoded as JSON string
 *
 * @param string $content The HTTP response body
 * @param int $code The HTTP response status code
 * @param string $charset The HTTP response charset
 *
 * @return int Returns 1, always
 */
function json($content, $code = 200, $charset = 'utf-8')
{
    return jsonstr(json_encode($content), $code, $charset);
}

/**
 * Helper method to setup a header item as key-value parts
 *
 * @param string $key The response header name
 * @param string $val The response header value
 * @param bool $replace Should replace a previous similar header, or add a second header of the same type.
 */
function header($key, $val, $replace = null)
{
    if (is_null($replace)) {
        $replace = true;
    }

    \header($key.': '.$val, $replace);
}
