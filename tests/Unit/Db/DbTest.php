<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Db;

class DbTest extends TestCase
{
    public function testQueryOutOfRange()
    {
        $this->expectException(\OutOfRangeException::class);
        Db\query('');
    }

    public function testPrepareOutOfRange()
    {
        $this->expectException(\OutOfRangeException::class);
        Db\prepare('');
    }

    public function testErrorOutOfRange()
    {
        $this->expectException(\OutOfRangeException::class);
        Db\error();
    }
}
