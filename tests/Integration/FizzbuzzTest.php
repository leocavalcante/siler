<?php

namespace Siler\Test\Integration;

use function Siler\Functional\always;
use function Siler\Functional\identity as id;
use function Siler\Functional\match;
use function Siler\Functional\mod;
use function Siler\Functional\not;

class FizzbuzzTest extends \PHPUnit\Framework\TestCase
{
    public function testFizzbuzz()
    {
        $input = range(1, 15);
        $expected = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'Fizz Buzz'];

        $match = match([
            [not(mod(15)), always('Fizz Buzz')],
            [not(mod(3)), always('Fizz')],
            [not(mod(5)), always('Buzz')],
            [always(true), id()],
        ]);

        $actual = array_map($match, $input);

        $this->assertEquals($expected, $actual);
    }
}
