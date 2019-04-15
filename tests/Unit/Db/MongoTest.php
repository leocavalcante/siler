<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use MongoDB\BSON\ObjectId;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Mongo;

class MongoTest extends TestCase
{
    public function testDatabaseOutOfRange()
    {
        $this->expectException(OutOfRangeException::class);
        Mongo\database('');
    }

    public function testCollectionOutOfRange()
    {
        $this->expectException(OutOfRangeException::class);
        Mongo\collection('', '');
    }

    public function testOid()
    {
        $oid = Mongo\oid('5a2493c33c95a1281836eb6a');
        $this->assertInstanceOf(ObjectId::class, $oid);
    }

    public function testUsing()
    {
        Mongo\using('test');
        $this->assertSame('test', Container\get(Mongo\MONGODB_USING_DBNAME));
    }
}
