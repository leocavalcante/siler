<?php
/**
 * Helper functions for webonyx/graphql-php GraphQL implementation.
 */

namespace Siler\Graphql;

use GraphQL\GraphQL;
use GraphQL\Schema;
use Siler\Http\Request;
use Siler\Http\Response;
use function Siler\array_get;

/**
 * Initializes a new GraphQL endpoint.
 *
 * @param Schema $schema    The application root Schema
 * @param mixed  $rootValue Some optional GraphQL root value
 * @param mixed  $context   Some optional GraphQL context
 * @param string $input     JSON file input, for testing
 */
function init(Schema $schema, $rootValue = null, $context = null, $input = 'php://input')
{
    if (Request\header('Content-Type') == 'application/json') {
        $data = Request\json($input);
    } else {
        $data = Request\post();
    }

    $query = array_get($data, 'query');
    $operation = array_get($data, 'operation');
    $variables = array_get($data, 'variables');

    $result = GraphQL::execute(
        $schema,
        $query,
        $rootValue,
        $context,
        $variables,
        $operation
    );

    Response\json($result);
}
