<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Db;

class DbTest extends TestCase
{
    /**
     * @expectedException \OutOfRangeException
     */
    public function testQueryOutOfRange()
    {
        Db\query('');
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testPrepareOutOfRange()
    {
        Db\prepare('');
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testErrorOutOfRange()
    {
        Db\error();
    }
}
