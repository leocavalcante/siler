<?php declare(strict_types=1);

namespace Siler\File;

/**
 * Returns a list of FileInfo objects recursively found in the given directory.
 *
 * @param string $dirname
 * @param string $regex
 * @param int $mode
 *
 * @return \SplFileInfo[]
 */
function recursively_iterated_directory(string $dirname, string $regex = '/.*/', $mode = \RegexIterator::MATCH): array
{
    $dir_iterator = new \RecursiveDirectoryIterator($dirname);
    $iterator = new \RecursiveIteratorIterator($dir_iterator);
    $regexp_iterator = new \RegexIterator($iterator, $regex, $mode);

    return array_values(iterator_to_array($regexp_iterator));
}
