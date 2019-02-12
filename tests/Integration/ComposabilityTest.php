<?php

declare(strict_types=1);

namespace Siler\Test\Integration;

use function Siler\Functional\all;
use function Siler\Functional\any;
use function Siler\Functional\compose;
use function Siler\Functional\equal;
use function Siler\Functional\mod;

class ComposabilityTest extends \PHPUnit\Framework\TestCase
{


    public function testCompose()
    {
        $isDivisibleBy3 = compose([equal(0), mod(3)]);

        $this->assertTrue($isDivisibleBy3(3));
        $this->assertFalse($isDivisibleBy3(2));
    }


    public function testAny()
    {
        $isDivisibleBy3    = compose([equal(0), mod(3)]);
        $isDivisibleBy5    = compose([equal(0), mod(5)]);
        $isDivisibleBy3Or5 = any([$isDivisibleBy3, $isDivisibleBy5]);

        $this->assertFalse($isDivisibleBy3Or5(2));
        $this->assertTrue($isDivisibleBy3Or5(3));
        $this->assertFalse($isDivisibleBy3Or5(4));
        $this->assertTrue($isDivisibleBy3Or5(5));
    }


    public function testAll()
    {
        $isDivisibleBy3     = compose([equal(0), mod(3)]);
        $isDivisibleBy5     = compose([equal(0), mod(5)]);
        $isDivisibleBy3And5 = all([$isDivisibleBy3, $isDivisibleBy5]);

        $this->assertFalse($isDivisibleBy3And5(2));
        $this->assertFalse($isDivisibleBy3And5(3));
        $this->assertFalse($isDivisibleBy3And5(4));
        $this->assertFalse($isDivisibleBy3And5(5));
        $this->assertTrue($isDivisibleBy3And5(15));
    }
}//end class
