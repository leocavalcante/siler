<?php

declare(strict_types=1);
/**
 * Adds a layer of helper functions to work with Zend Diactoros.
 */

namespace Siler\Diactoros;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Returns a new ServerRequest from globals.
 *
 * @return ServerRequest
 */
function request() : ServerRequest
{
    return ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
}

/**
 * Returns a new response.
 *
 * @param string $body
 * @param int    $status
 * @param array  $headers
 *
 * @return Response
 */
function response(string $body = 'php://memory', int $status = 200, array $headers = []) : Response
{
    return new Response($body, $status, $headers);
}

/**
 * Returns a new HTML response.
 *
 * @param string $html
 * @param int    $status
 * @param array  $headers
 *
 * @return HtmlResponse
 */
function html(string $html, int $status = 200, array $headers = []) : HtmlResponse
{
    return new HtmlResponse($html, $status, $headers);
}

/**
 * Returns a new JSON encoded response.
 *
 * @param mixed $data
 * @param int   $status
 * @param array $headers
 * @param int   $encodingOptions
 *
 * @return JsonResponse
 */
function json($data, int $status = 200, array $headers = [], int $encodingOptions = JsonResponse::DEFAULT_JSON_FLAGS) : JsonResponse
{
    return new JsonResponse($data, $status, $headers, $encodingOptions);
}

/**
 * Returns a new text response.
 *
 * @param string $text
 * @param int    $status
 * @param array  $headers
 *
 * @return TextResponse
 */
function text(string $text, int $status = 200, array $headers = []) : TextResponse
{
    return new TextResponse($text, $status, $headers);
}

/**
 * Returns a new redirect response.
 *
 * @param string $uri
 * @param int    $status
 * @param array  $headers
 *
 * @return RedirectResponse
 */
function redirect(string $uri, int $status = 302, array $headers = []) : RedirectResponse
{
    return new RedirectResponse($uri, $status, $headers);
}

/**
 * Returns a new empty response.
 *
 * @param int   $status
 * @param array $headers
 *
 * @return EmptyResponse
 */
function none(int $status = 204, array $headers = []) : EmptyResponse
{
    return new EmptyResponse($status, $headers);
}
