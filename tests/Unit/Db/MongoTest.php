<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Mongo;
use Siler\Container;

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

    public function testOid()
    {
        $oid = Mongo\oid('5a2493c33c95a1281836eb6a');
        $this->assertInstanceOf(\MongoDB\BSON\ObjectId::class, $oid);
    }

    public function testUsing()
    {
        Mongo\using('test');
        $this->assertSame('test', Container\get(Mongo\MONGODB_USING_DBNAME));
    }
}
