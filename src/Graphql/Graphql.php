<?php
/**
 * Helper functions for webonyx/graphql-php GraphQL implementation.
 */

namespace Siler\Graphql;

use GraphQL\Executor\Executor;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Ratchet\Client;
use Ratchet\Client\WebSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Siler\Container;
use Siler\Http\Request;
use Siler\Http\Response;
use function Siler\array_get;

const INIT = 'init';
const INIT_SUCCESS = 'init_success';
const INIT_FAIL = 'init_fail';
const SUBSCRIPTION_START = 'subscription_start';
const SUBSCRIPTION_END = 'subscription_end';
const SUBSCRIPTION_SUCCESS = 'subscription_success';
const SUBSCRIPTION_FAIL = 'subscription_fail';
const SUBSCRIPTION_DATA = 'subscription_data';

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
 * Returns a new GraphQL\Schema from a file containing GraphQL language.
 * Also sets a Siler's default field resolver based on $resolvers array.
 *
 * @param string $typeDefs
 * @param array  $resolvers
 *
 * @return Schema
 */
function schema($typeDefs, array $resolvers = [])
{
    if (!empty($resolvers)) {
        resolvers($resolvers);
    }

    return BuildSchema::build($typeDefs);
}

/**
 * Sets a Siler's default field resolver based on the given $resolvers array.
 *
 * @param array $resolvers
 */
function resolvers(array $resolvers)
{
    Executor::setDefaultFieldResolver(function ($source, $args, $context, ResolveInfo $info) use ($resolvers) {
        $fieldName = $info->fieldName;
        $parentTypeName = $info->parentType->name;

        if (isset($resolvers[$parentTypeName])) {
            $resolver = $resolvers[$parentTypeName];

            if (is_array($resolver)) {
                if (array_key_exists($fieldName, $resolver)) {
                    $value = $resolver[$fieldName];

                    return is_callable($value) ? $value($source, $args, $context) : $value;
                }
            }

            if (is_object($resolver)) {
                if (isset($resolver->{$fieldName})) {
                    $value = $resolver->{$fieldName};

                    return is_callable($value) ? $value($source, $args, $context) : $value;
                }
            }
        }

        return Executor::defaultFieldResolver($source, $args, $context, $info);
    });
}

/**
 * Returns a new websocket server bootstraped for GraphQL subscriptions.
 *
 * @param Schema $schema
 * @param array  $filters
 * @param array  $rootValue
 * @param array  $context
 * @param int    $port
 * @param string $host
 *
 * @return IoServer
 */
function subscriptions(
    Schema $schema,
    array $filters = null,
    array $rootValue = null,
    array $context = null,
    $port = 8080,
    $host = '0.0.0.0'
) {
    $manager = new SubscriptionManager($schema, $filters, $rootValue, $context);
    $server = new SubscriptionServer($manager);

    $websocket = new WsServer($server);

    $http = new HttpServer($websocket);

    return IoServer::factory($http, $port, $host);
}

/**
 * Sets the GraphQL server endpoint where publish should connect to.
 *
 * @param string $url
 */
function subscriptions_at($url)
{
    Container\set('graphql_subscriptions_endpoint', $url);
}

/**
 * Publishes the given $payload to the $subscribeName.
 *
 * @param string $subscriptionName
 * @param mixed  $payload
 */
function publish($subscriptionName, $payload = null)
{
    $subscriptionsEndpoint = Container\get('graphql_subscriptions_endpoint');

    Client\connect($subscriptionsEndpoint)->then(function (WebSocket $conn) use ($subscriptionName, $payload) {
        $request = [
            'type'         => SUBSCRIPTION_DATA,
            'subscription' => $subscriptionName,
            'payload'      => $payload,
        ];

        $conn->send(json_encode($request));
        $conn->close();
    });
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
                return new $resolve();
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
 * @return StringType|\Closure -> (resolve, args) -> array
 */
function str($name = null, $description = null)
{
    if (is_null($name)) {
        return Type::string();
    }

    return field(Type::string(), $name, $description);
}

/**
 * Returns an evaluable Integer field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return IntType|\Closure -> (resolve, args) -> array
 */
function int($name = null, $description = null)
{
    if (is_null($name)) {
        return Type::int();
    }

    return field(Type::int(), $name, $description);
}

/**
 * Returns an evaluable Float field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return FloatType|\Closure -> (resolve, args) -> array
 */
function float($name = null, $description = null)
{
    if (is_null($name)) {
        return Type::float();
    }

    return field(Type::float(), $name, $description);
}

/**
 * Returns an evaluable Boolean field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return BooleanType|\Closure -> (resolve, args) -> array
 */
function bool($name = null, $description = null)
{
    if (is_null($name)) {
        return Type::boolean();
    }

    return field(Type::boolean(), $name, $description);
}

function list_of(Type $type, $name = null, $description = null)
{
    if (is_null($name)) {
        /** @psalm-suppress TypeCoercion  */
        return Type::listOf($type);
    }

    /** @psalm-suppress TypeCoercion  */
    return field(Type::listOf($type), $name, $description);
}

/**
 * Returns an evaluable Id field definition.
 *
 * @param string $name
 * @param string $description
 *
 * @return IDType|\Closure -> (resolve, args) -> array
 */
function id($name = null, $description = null)
{
    if (is_null($name)) {
        return Type::id();
    }

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
