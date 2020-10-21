<?php declare(strict_types=1);

namespace Siler\Test\Unit\File;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use function Siler\File\concat_files;
use function Siler\File\join_dir;
use function Siler\File\recur_iter_dir;
use function Siler\File\recursively_iterated_directory;

class FileTest extends TestCase
{
    public function testRecursivelyIteratedDirectory()
    {
        $basedir = dirname(__DIR__, 2);
        $dir = recursively_iterated_directory(join_dir($basedir, 'fixtures'));

        $dir = array_map(function (SplFileInfo $info): string {
            return $info->getPathname();
        }, $dir);

        $this->assertContains(join_dir($basedir, 'fixtures', 'foo.php'), $dir);
    }

    public function testRecursivelyIteratedDirectoryWithPattern()
    {
        $basedir = dirname(__DIR__, 2);
        $dir = recur_iter_dir(join_dir($basedir, 'fixtures'), '/\.txt$/');

        $dir = array_map(function (SplFileInfo $info): string {
            return $info->getPathname();
        }, $dir);

        $this->assertContains(join_dir($basedir, 'fixtures', 'php_input.txt'), $dir);
    }

    public function testConcatFilesWithRecur()
    {
        $basedir = dirname(__DIR__, 2);
        $result = concat_files(recur_iter_dir(join_dir($basedir, 'fixtures', 'concat')), '');

        // Note: file order is arbitrary
        $this->assertTrue($result === 'foobar' || $result === 'barfoo');
    }

    public function testConcatFiles()
    {
        $result = concat_files([__DIR__ . '/../../fixtures/concat/foo.txt', __DIR__ . '/../../fixtures/concat/bar.txt']);
        $this->assertSame("foo\nbar", $result);
    }
}
