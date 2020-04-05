<?php declare(strict_types=1);

namespace Siler\Test\Unit\GraphQL;

use PHPUnit\Framework\TestCase;
use Siler\Test\Unit\GraphQL\Annotated\Input;
use Siler\Test\Unit\GraphQL\Annotated\Mutation;
use Siler\Test\Unit\GraphQL\Annotated\Query;
use function Siler\GraphQL\annotated;
use function Siler\GraphQL\execute;

class AnnotatedTest extends TestCase
{
    public function testAnnotated()
    {
        $schema = annotated([Input::class, Query::class, Mutation::class]);
        $schema->assertValid();

        $this->assertTrue($schema->hasType('Query'));
        $this->assertTrue($schema->hasType('Mutation'));

        $result = execute($schema, ['query' => 'query { hello }']);
        $this->assertSame(['data' => ['hello' => 'world']], $result);

        $result = execute($schema, ['query' => 'mutation { sum(input: {x: 2, y: 2}) }']);
        $this->assertSame(['data' => ['sum' => 4]], $result);
    }
}
