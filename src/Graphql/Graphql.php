<?php
/**
 * Helper functions for webonyx/graphql-php GraphQL implementation.
 */

namespace Siler\Graphql;

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
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

/**
 * Returns a GraphQL value definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> value -> array
 */
function val($name, $description = null)
{
    return function ($value) use ($name, $description) {
        return compact('name', 'description', 'value');
    };
}

/**
 *  Returns a GraphQL Enum type.
 *
 * @param $name
 * @param $description
 *
 * @return \Closure -> values -> EnumType
 */
function enum($name, $description = null)
{
    return function (array $values) use ($name, $description) {
        return new EnumType(compact('name', 'description', 'values'));
    };
}

/**
 * Returns an evaluable field definition.
 *
 * @param Type   $type
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function field(Type $type, $name, $description = null)
{
    return function ($resolve = null, array $args = null) use ($type, $name, $description) {
        if (is_string($resolve)) {
            $resolve = function () use ($resolve) {
                return new $resolve;
            };
        }

        return compact('type', 'name', 'description', 'resolve', 'args');
    };
}

/**
 * Returns an evaluable String field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function str($name, $description = null)
{
    return field(Type::string(), $name, $description);
}

/**
 * Returns an evaluable Integer field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function int($name, $description = null)
{
    return field(Type::int(), $name, $description);
}

/**
 * Returns an evaluable Float field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function float($name, $description = null)
{
    return field(Type::float(), $name, $description);
}

/**
 * Returns an evaluable Boolean field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function bool($name, $description = null)
{
    return field(Type::boolean(), $name, $description);
}

/**
 * Returns an evaluable Id field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> (resolve, args) -> array
 */
function id($name, $description = null)
{
    return field(Type::id(), $name, $description);
}

/**
 * Returns an InterfaceType factory function.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> fields -> resolve -> InterfaceType
 */
function itype($name, $description = null)
{
    return function (array $fields = []) use ($name, $description) {
        return function (callable $resolveType) use ($name, $description, $fields) {
            return new InterfaceType(compact('name', 'description', 'fields', 'resolveType'));
        };
    };
}

/**
 * Returns an ObjectType factory function.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> fields -> resolve -> ObjectType
 */
function type($name, $description = null)
{
    return function (array $fields = []) use ($name, $description) {
        return function (callable $resolve = null) use ($name, $description, $fields) {
            return new ObjectType(compact('name', 'description', 'fields', 'resolve'));
        };
    };
}
