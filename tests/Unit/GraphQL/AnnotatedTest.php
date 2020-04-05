<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

use PHPUnit\Framework\TestCase;
use Siler\Test\Unit\GraphQL\Annotated\Query;
use function Siler\GraphQL\annotated;
use function Siler\GraphQL\execute;

class AnnotatedTest extends TestCase
{
    public function testAnnotated()
    {
        $schema = annotated([Query::class]);
        $schema->assertValid();

        $this->assertTrue($schema->hasType('Query'));

        $result = execute($schema, ['query' => 'query { hello }']);
        $this->assertSame(['data' => ['hello' => 'world']], $result);
    }
}
