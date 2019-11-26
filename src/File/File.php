<?php declare(strict_types=1);

namespace Siler\File;

use function Siler\Functional\concat;

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

/**
 * Alias for recursively_iterated_directory().
 *
 * @param string $dirname
 * @param string $regex
 * @param int $mode
 *
 * @return \SplFileInfo[]
 */
function recur_iter_dir(string $dirname, string $regex = '/.*/', $mode = \RegexIterator::MATCH): array
{
    return recursively_iterated_directory($dirname, $regex, $mode);
}

/**
 * Loads and concatenates file contents.
 *
 * @param string[]|\SplFileInfo[] $files
 *
 * @return string
 */
function concat_files(array $files): string
{
    $files = array_map('file_get_contents', $files);
    return trim(array_reduce($files, concat("\n"), ''));
}
