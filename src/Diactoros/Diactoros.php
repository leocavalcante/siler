<?php
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
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Returns a new ServerRequest from globals.
 *
 * @return ServerRequest
 */
function request()
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
function response($body = 'php://memory', $status = 200, array $headers = [])
{
    return new Response($body, $status, $headers);
}

/**
 * Emits a PSR-7 SAPI response.
 *
 * @param ResponseInterface $response
 */
function emit(ResponseInterface $response)
{
    (new SapiEmitter())->emit($response);
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
function html($html, $status = 200, array $headers = [])
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
function json($data, $status = 200, array $headers = [], $encodingOptions = JsonResponse::DEFAULT_JSON_FLAGS)
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
function text($text, $status = 200, array $headers = [])
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
function redirect($uri, $status = 302, array $headers = [])
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
function none($status = 204, array $headers = [])
{
    return new EmptyResponse($status, $headers);
}
