<?php declare(strict_types=1);

namespace Siler\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use function Siler\Functional\concat;

/**
 * Returns a list of FileInfo objects recursively found in the given directory.
 *
 * @param string $dirname
 * @param string $regex
 * @param int(0)|int(1)|int(2)|int(3)|int(4) $mode
 *
 * @psalm-return list<SplFileInfo>
 * @return SplFileInfo[]
 */
function recursively_iterated_directory(string $dirname, string $regex = '/.*/', $mode = RegexIterator::MATCH): array
{
    $dir_iterator = new RecursiveDirectoryIterator($dirname);
    $iterator = new RecursiveIteratorIterator($dir_iterator);
    $regexp_iterator = new RegexIterator($iterator, $regex, $mode);

    $list = [];

    /** @var SplFileInfo $spl_file_info */
    foreach ($regexp_iterator as $spl_file_info) {
        $list[] = $spl_file_info;
    }

    return $list;
}

/**
 * Alias for recursively_iterated_directory().
 *
 * @param string $dirname
 * @param string $regex
 * @param int $mode
 *
 * @psalm-return list<SplFileInfo>
 * @return SplFileInfo[]
 */
function recur_iter_dir(string $dirname, string $regex = '/.*/', $mode = RegexIterator::MATCH): array
{
    return recursively_iterated_directory($dirname, $regex, $mode);
}

/**
 * Loads and concatenates file contents.
 *
 * @param string[]|SplFileInfo[] $files
 * @param string $separator
 *
 * @return string
 */
function concat_files(array $files, string $separator = "\n"): string
{
    $files = array_filter(
        $files,
        /**
         * @param string|SplFileInfo $file
         * @return bool
         */
        static function ($file): bool {
            if ($file instanceof SplFileInfo) {
                $file->isFile();
            }

            return is_file((string)$file);
        }
    );

    $files = array_map(
    /**
     * @param string|SplFileInfo $file
     */
        static function ($file): string {
            if ($file instanceof SplFileInfo) {
                return file_get_contents($file->getPathname());
            }

            return file_get_contents($file);
        },
        $files
    );

    /** @var string $contents */
    $contents = array_reduce($files, concat($separator), '');

    return trim($contents);
}

/**
 * @param array<string> ...$segments
 *
 * @return string
 */
function join_dir(...$segments): string
{
    return join(DIRECTORY_SEPARATOR, $segments);
}
