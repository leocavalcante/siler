<?php
/**
 * Helper functions for webonyx/graphql-php GraphQL implementation.
 */

namespace Siler\Graphql;

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
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

function val($name, $description = null)
{
    return function ($value) use ($name, $description) {
        return compact('name', 'description', 'value');
    };
}

function enum($name, $description = null)
{
    return function (array $values) use ($name, $description) {
        return new EnumType(compact('name', 'description', 'values'));
    };
}

function field($type, $name, $description = null)
{
    return function (callable $resolve = null) use ($type, $name, $description) {
        return compact('type', 'name', 'description', 'resolve');
    };
}

function str($name, $description = null)
{
    return field(Type::string(), $name, $description);
}

function itype($name, $description = null)
{
    return function (array $fields) use ($name, $description) {
        return function (callable $resolveType) use ($name, $description, $fields) {
            return new InterfaceType(compact('name', 'description', 'fields', 'resolveType'));
        };
    };
}

function type($name, $description = null)
{
    return function (array $fields) use ($name, $description) {
        return function (callable $resolve = null) use ($name, $description, $fields) {
            return new ObjectType(compact('name', 'description', 'fields', 'resolve'));
        };
    };
}
