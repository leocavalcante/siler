<?php declare(strict_types=1);

namespace Siler\Test\Unit\File;

use PHPUnit\Framework\TestCase;
use function Siler\File\recursively_iterated_directory;

class FileTest extends TestCase
{
    public function testRecursivelyIteratedDirectory()
    {
        $basedir = dirname(__DIR__, 2);
        $dir = recursively_iterated_directory("$basedir/fixtures");

        $this->assertContains("$basedir/fixtures/foo.php", $dir);
    }

    public function testRecursivelyIteratedDirectoryWithPattern()
    {
        $basedir = dirname(__DIR__, 2);
        $dir = recursively_iterated_directory("$basedir/fixtures", '/\.txt$/');

        $this->assertContains("$basedir/fixtures/php_input.txt", $dir);
    }
}
