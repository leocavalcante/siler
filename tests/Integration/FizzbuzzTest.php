<?php declare(strict_types=1);

namespace Siler\Test\Integration;

use PHPUnit\Framework\TestCase;
use function Siler\Functional\{always, identity, match, mod, not};

class FizzbuzzTest extends TestCase
{
    public function testFizzBuzz()
    {
        $input = range(1, 15);
        $expected = [1, 2, 'Fizz', 4, 'Buzz', 'Fizz', 7, 8, 'Fizz', 'Buzz', 11, 'Fizz', 13, 14, 'Fizz Buzz'];

        $match = match([
            [not(mod(15)), always('Fizz Buzz')],
            [not(mod(3)), always('Fizz')],
            [not(mod(5)), always('Buzz')],
        ], identity());

        $actual = array_map($match, $input);

        $this->assertSame($expected, $actual);
    }
}
