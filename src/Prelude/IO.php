<?php declare(strict_types=1);

namespace Siler\IO;

use function Siler\Encoder\Json\encode;

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

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($opts['method']),
        CURLOPT_FOLLOWLOCATION => $opts['follow'],
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $opts['body'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => $opts['verify'],
        CURLOPT_TIMEOUT => $opts['timeout'],
        CURLOPT_URL => $opts['url'],
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    if (empty($response)) {
        $error = curl_error($ch);
        $response = null;
    }

    curl_close($ch);

    return [
        'error' => $error,
        'response' => $response,
        'status' => $status,
    ];
}
