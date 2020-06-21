<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use Siler\Prelude\FromArray;
use Siler\Prelude\FromArrayInterface;
use Siler\Prelude\Patch;
use Siler\Prelude\PatchInterface;
use Siler\Prelude\ToArray;
use Siler\Prelude\ToArrayInterface;

/**
 * @implements FromArrayInterface<self>
 * @implements PatchInterface<self>
 */
class PatchFromToFixture implements FromArrayInterface, ToArrayInterface, PatchInterface
{
    /** @use FromArray<self> */
    use FromArray;
    use ToArray;

    /** @use Patch<self> */
    use Patch;

    public $foo;
    public $fooBar;
    public $fooBarBaz;
}
