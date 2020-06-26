<?php declare(strict_types=1);

namespace Siler\IO;

use function Siler\array_get;
use function Siler\array_get_bool;
use function Siler\array_get_int;
use function Siler\array_get_str;
use function Siler\Encoder\Json\encode;
use function Siler\Str\starts_with;

/**
 * Prints a string to the output and adds an EOL.
 *
 * @param string $str
 */
function println(string $str): void
{
    echo $str, PHP_EOL;
}

/**
 * Returns a CSV-file contents as an array.
 *
 * @param string $filename
 * @param string $delimiter
 * @param int $length
 * @return array
 */
function csv_to_array(string $filename, string $delimiter = ',', int $length = 0): array
{
    $handle = fopen($filename, 'r');
    $arr = [];

    while ($row = fgetcsv($handle, $length, $delimiter)) {
        $arr[] = $row;
    }

    fclose($handle);

    return $arr;
}

/**
 * Simple HTTP request.
 *
 * @psalm-type RequestOpts=array{
 *   body?: string,
 *   follow?: bool,
 *   headers?: array<string, string>,
 *   method?: string,
 *   timeout?: int,
 *   url?: string,
 *   verify?: bool,
 *   json?: mixed,
 *   query?: array<string, mixed>,
 *   parse?: bool,
 * }
 *
 * @psalm-type Json=array|string|int|float|bool|null
 *
 * @psalm-type Response=array{
 *   error: string,
 *   headers: array<string, string>,
 *   response: string|Json,
 *   status: int,
 * }
 *
 * @param string $url
 * @param array $opts
 * @psalm-param RequestOpts $opts
 * @return array
 */
function fetch(string $url, array $opts = []): array
{
    $error = null;
    $headers = [];
    $ch = curl_init();

    if (array_key_exists('json', $opts) && array_key_exists('body', $opts)) {
        $headers[] = 'Content-Type: application/json';
        $opts['body'] = encode($opts['body']);
    }

    if (array_key_exists('headers', $opts)) {
        foreach ($opts['headers'] as $h_name => $h_value) {
            $headers[] = "$h_name: $h_value";
        }
    }

    $url = array_get_str($opts, 'url', $url);

    if (array_key_exists('query', $opts)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($opts['query']);
    }

    /** @psalm-var array<string, string> $response_headers */
    $response_headers = [];

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper(array_get_str($opts, 'method', 'get')),
        CURLOPT_FOLLOWLOCATION => array_get_bool($opts, 'follow', true),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => array_get($opts, 'body', null),
        CURLOPT_SSL_VERIFYPEER => array_get_bool($opts, 'verify', false),
        CURLOPT_TIMEOUT => array_get_int($opts, 'timeout', 30),
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADERFUNCTION =>
        /**
         * @param mixed $ch
         * @param string $header
         * @return int
         */
            static function ($ch, string $header) use (&$response_headers): int {
                $len = strlen($header);
                $header = explode(':', $header, 2);

                if (count($header) < 2) {
                    return $len;
                }

                $response_headers[strtolower(trim($header[0]))] = trim($header[1]);

                return $len;
            },
    ]);

    /** @var array<string, string> $response_headers */
    $response_headers = $response_headers;
    $response = curl_exec($ch);
    $status = intval(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
    $data = null;

    if ($response === false) {
        $error = curl_error($ch);

        if ($error === '') {
            $error = null;
        }

        $response = null;
    }

    curl_close($ch);

    if (array_get_bool($opts, 'parse', true) && array_key_exists('content-type', $response_headers)) {
        if (starts_with($response_headers['content-type'], 'application/json') && is_string($response)) {
            /** @psalm-var Json $response */
            $response = json_decode($response, true);

            if (json_last_error()) {
                $error = json_last_error_msg();
                $response = null;
            }
        }
    }

    return [
        'error' => $error,
        'headers' => $response_headers,
        'response' => $response,
        'status' => $status,
    ];
}
