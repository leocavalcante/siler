<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use Siler\Prelude\FromArray;
use Siler\Prelude\ToArray;

/**
 * Class FromToArrayFixture
 * @package Siler\Test\Unit\Prelude
 */
class FromToArrayFixture
{
    use FromArray;
    use ToArray;

    public $foo;
    public $fooBar;
    public $fooBarBaz;
}
