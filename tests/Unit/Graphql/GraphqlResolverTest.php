<?php

declare(strict_types=1);

namespace Siler\Test\Unit\Graphql;

use Siler\GraphQL;

class GraphqlResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testResolver()
    {
        $typeDefs = '
            type Query {
                message: String
            }
        ';

        $resolvers = [
            'Query' => [
                'message' => 'foo',
            ],
        ];

        $expected = [
            'data' => [
                'message' => 'foo',
            ],
        ];

        $query = 'query { message }';
        $schema = GraphQL\schema($typeDefs, $resolvers);
        $actual = \GraphQL\GraphQL::executeQuery($schema, $query)->toArray();

        $this->assertSame($expected, $actual);
    }

    public function testCallableResolver()
    {
        $typeDefs = '
            type Query {
                message: String
            }
        ';

        $resolvers = [
            'Query' => [
                'message' => function ($root, $args) {
                    return 'foo';
                },
            ],
        ];

        $expected = [
            'data' => [
                'message' => 'foo',
            ],
        ];

        $query = 'query { message }';
        $schema = GraphQL\schema($typeDefs, $resolvers);
        $actual = \GraphQL\GraphQL::executeQuery($schema, $query)->toArray();

        $this->assertSame($expected, $actual);
    }

    public function testMutation()
    {
        $typeDefs = '
            type Query {
                message: String
            }

            type Mutation {
                sum(a: Int, b: Int): Int
            }
        ';

        $resolvers = [
            'Mutation' => [
                'sum' => function ($root, $args) {
                    return $args['a'] + $args['b'];
                },
            ],
        ];

        $expected = [
            'data' => [
                'sum' => 4,
            ],
        ];

        $query = 'mutation { sum(a: 2, b: 2) }';
        $schema = GraphQL\schema($typeDefs, $resolvers);
        $actual = \GraphQL\GraphQL::executeQuery($schema, $query)->toArray();

        $this->assertSame($expected, $actual);
    }

    public function testObjectResolve()
    {
        $object = new \stdClass();
        $object->message = 'foo';

        $typeDefs = '
            type Query {
                message: String
            }
        ';

        $resolvers = [
            'Query' => $object,
        ];

        $expected = [
            'data' => [
                'message' => 'foo',
            ],
        ];

        $query = 'query { message }';
        $schema = GraphQL\schema($typeDefs, $resolvers);
        $actual = \GraphQL\GraphQL::executeQuery($schema, $query)->toArray();

        $this->assertSame($expected, $actual);
    }
}
