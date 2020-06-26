<?php declare(strict_types=1);

namespace Siler\IO;

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

function fetch(string $url, array $opts = [])
{
    $opts = array_merge([
        'body' => '',
        'follow' => true,
        'headers' => [],
        'method' => 'get',
        'timeout' => 30,
        'url' => $url,
        'verify' => false,
        'json' => null,
        'query' => [],
        'parse' => true,
    ], $opts);

    $error = null;
    $headers = [];
    $ch = curl_init();

    if ($opts['json'] !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts['body'] = encode($opts['body']);
    }

    foreach ($opts['headers'] as $h_name => $h_value) {
        $headers[] = "$h_name: $h_value";
    }

    $url = $opts['url'];

    if (!empty($opts['query'])) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($opts['query']);
    }

    $response_headers = [];

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($opts['method']),
        CURLOPT_FOLLOWLOCATION => $opts['follow'],
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $opts['body'],
        CURLOPT_SSL_VERIFYPEER => $opts['verify'],
        CURLOPT_TIMEOUT => $opts['timeout'],
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$response_headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);

            if (count($header) < 2) {
                return $len;
            }

            $response_headers[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        },
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $data = null;

    if (empty($response)) {
        $error = curl_error($ch);
        $response = null;
    }

    curl_close($ch);

    if ($opts['parse']) {
        if (starts_with($response_headers['content-type'], 'application/json')) {
            $data = json_decode($response, true);

            if (json_last_error()) {
                $error = json_last_error_msg();
                $data = null;
                $response = null;
            }
        }
    }

    return [
        'error' => $error,
        'response' => $response,
        'status' => $status,
        'headers' => $response_headers,
        'data' => $data,
    ];
}
