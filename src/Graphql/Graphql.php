<?php
/**
 * Helper functions for webonyx/graphql-php GraphQL implementation
 */

namespace Siler\Graphql;

use Siler\Http;
use Siler\Http\Response;
use GraphQL\Schema;
use GraphQL\GraphQL;
use \Exception;
use function Siler\array_get;

/**
 * Initializes a new GraphQL endpoint
 *
 * @param  Schema $schema    The application root Schema
 * @param   mixed $rootValue Some optional GraphQL root value
 * @param   mixed $context   Some optional GraphQL context
 */
function init(Schema $schema, $rootValue = null, $context = null)
{
    if (Request\header('Content-Type') == 'application/json') {
        $data = Request\json();
    } else {
        $data = Http\post();
    }

    $query = array_get($data, 'query');
    $operation = array_get($data, 'operation');
    $variables = array_get($data, 'variables');

    try {
        $result = GraphQL::execute(
            $schema,
            $query,
            $rootValue,
            $context,
            $variables,
            $operation
        );
    } catch (Exception $exception) {
        $result = [
            'error' => [
                'message' => $exception->getMessage()
            ]
        ];
    }

    Response\json($result);
}
