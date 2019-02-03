<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Mongo;

class MongoTest extends TestCase
{
    /**
     * @expectedException \OutOfRangeException
     */
    public function testDatabaseOutOfRange()
    {
        Mongo\database('');
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testCollectionOutOfRange()
    {
        Mongo\collection('', '');
    }
}
