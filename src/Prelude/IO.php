<?php declare(strict_types=1);

namespace Siler\IO;

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

    return $arr;
}
