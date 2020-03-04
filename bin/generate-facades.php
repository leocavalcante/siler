<?php declare(strict_types=1);

namespace Siler;

use function Siler\File\recur_iter_dir;
use function Siler\Functional\flatten;

$basedir = dirname(__DIR__);
require_once "$basedir/vendor/autoload.php";

$files = recur_iter_dir("$basedir/src/", '/\.php$/');
$contents = array_map('file_get_contents', $files);

$facades = array_map(function (string $content): array {
    preg_match_all('/namespace (.*);/', $content, $matches);
    $namespace = $matches[1][0] ?? '';

    preg_match_all('/\nfunction ([A-z0-9_]+)\(/', $content, $matches);

    if (empty($matches[1])) {
        return [];
    }

    return ["namespace $namespace;", array_map(function (string $funcName) use ($namespace): string {
        return "const $funcName = '\\$namespace\\$funcName';";
    }, $matches[1]), "\n"];
}, $contents);

$lines = flatten($facades);
$head = <<<HEAD
<?php
/**
 * @noinspection PhpConstantNamingConventionInspection
 * @noinspection PhpUnused
 */

declare(strict_types=1);\n
HEAD;

array_unshift($lines, $head);
file_put_contents("$basedir/src/facades.php", join("\n", $lines));
