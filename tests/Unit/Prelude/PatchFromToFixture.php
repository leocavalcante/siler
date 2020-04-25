<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use Siler\Prelude\FromArray;
use Siler\Prelude\Patch;
use Siler\Prelude\ToArray;

/**
 * Class FromToArrayFixture
 * @package Siler\Test\Unit\Prelude
 */
class PatchFromToFixture
{
    use FromArray;
    use ToArray;
    use Patch;

    public $foo;
    public $fooBar;
    public $fooBarBaz;
}
