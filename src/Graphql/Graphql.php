<?php

declare(strict_types=1);
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
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Client;
use Ratchet\Client\WebSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Siler\Container;
use Siler\Diactoros;
use Siler\Http\Request;
use Siler\Http\Response;
use function Siler\array_get;

/**
 * Protocol messages.
 *
 * @see https://github.com/apollographql/subscriptions-transport-ws/blob/master/src/message-types.ts
 */
const GQL_CONNECTION_INIT = 'connection_init'; // Client -> Server
const GQL_CONNECTION_ACK = 'connection_ack'; // Server -> Client
const GQL_CONNECTION_ERROR = 'connection_error'; // Server -> Client
const GQL_CONNECTION_KEEP_ALIVE = 'ka'; // Server -> Client
const GQL_CONNECTION_TERMINATE = 'connection_terminate'; // Client -> Server
const GQL_START = 'start'; // Client -> Server
const GQL_DATA = 'data'; // Server -> Client
const GQL_ERROR = 'error'; // Server -> Client
const GQL_COMPLETE = 'complete'; // Server -> Client
const GQL_STOP = 'stop'; // Client -> Server

/**
 * Initializes a new GraphQL endpoint.
 *
 * @param Schema $schema    The application root Schema
 * @param mixed  $rootValue Some optional GraphQL root value
 * @param mixed  $context   Some optional GraphQL context
 * @param string $input     JSON file input, for testing
 *
 * @return void
 */
function init(Schema $schema, $rootValue = null, $context = null, string $input = 'php://input')
{
    if (Request\header('Content-Type') == 'application/json') {
        $data = Request\json($input);
    } else {
        $data = Request\post();
    }

    $result = execute($schema, $data, $rootValue, $context);

    Response\json($result);
}

/**
 * Executes a GraphQL query over a schema.
 *
 * @param Schema $schema    The application root Schema
 * @param array  $input     Incoming query, operation and variables
 * @param mixed  $rootValue Some optional GraphQL root value
 * @param mixed  $context   Some optional GraphQL context
 *
 * @return array<mixed, mixed>|\GraphQL\Executor\Promise\Promise
 */
function execute(Schema $schema, array $input, $rootValue = null, $context = null)
{
    $query = array_get($input, 'query');
    $operation = array_get($input, 'operationName');
    $variables = array_get($input, 'variables');

    return GraphQL::execute(
        $schema,
        $query,
        $rootValue,
        $context,
        $variables,
        $operation
    );
}

/**
 * Returns a PSR-7 complaint ServerRequestInterface handler.
 *
 * @param Schema $schema GraphQL schema to execute
 *
 * @return \Closure ServerRequestInterface -> IO
 */
function psr7(Schema $schema) : \Closure
{
    return function (ServerRequestInterface $request) use ($schema) {
        $input = json_decode((string) $request->getBody(), true);
        $data = execute($schema, $input);

        return Diactoros\json($data);
    };
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
function schema(string $typeDefs, array $resolvers = []) : Schema
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
 *
 * @return void
 */
function resolvers(array $resolvers)
{
    /*
     * @psalm-suppress MissingClosureParamType
     */
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
 * Returns a new websocket server bootstraped for GraphQL.
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
function ws(
    Schema $schema,
    array $filters = null,
    string $host = '0.0.0.0',
    int $port = 5000,
    array $rootValue = null,
    array $context = null
) : IoServer {
    $manager = new WsManager($schema, $filters, $rootValue, $context);
    $server = new WsServer($manager);
    $websocket = new \Ratchet\WebSocket\WsServer($server);
    $http = new HttpServer($websocket);

    return IoServer::factory($http, $port, $host);
}

/**
 * Sets the GraphQL server endpoint where publish should connect to.
 *
 * @param string $url
 *
 * @return void
 */
function ws_endpoint(string $url)
{
    Container\set('graphql_ws_endpoint', $url);
}

/**
 * Publishes the given $payload to the $subscribeName.
 *
 * @param string $subscriptionName
 * @param mixed  $payload
 *
 * @return void
 */
function publish(string $subscriptionName, $payload = null)
{
    $wsEndpoint = Container\get('graphql_ws_endpoint');

    Client\connect($wsEndpoint, ['graphql-ws'])->then(function (WebSocket $conn) use ($subscriptionName, $payload) {
        $request = [
            'type'         => GQL_DATA,
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
function val(string $name, string $description = null) : \Closure
{
    return function ($value) use ($name, $description) : array {
        return compact('name', 'description', 'value');
    };
}

/**
 *  Returns a GraphQL Enum type.
 *
 * @param string $name
 * @param string $description
 *
 * @return \Closure -> values -> EnumType
 */
function enum(string $name, string $description = null) : \Closure
{
    return function (array $values) use ($name, $description) : EnumType {
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
function field(Type $type, string $name, string $description = null) : \Closure
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
function str(string $name = null, string $description = null)
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
function int(string $name = null, string $description = null)
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
function float(string $name = null, string $description = null)
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
function bool(string $name = null, string $description = null)
{
    if (is_null($name)) {
        return Type::boolean();
    }

    return field(Type::boolean(), $name, $description);
}

/**
 * @param Type   $type
 * @param string $name
 * @param string $description
 *
 * @return ListOfType|\Closure -> (resolve, args) -> array
 *
 * @psalm-suppress TypeCoercion
 */
function list_of(Type $type, string $name = null, string $description = null)
{
    if (is_null($name)) {
        return Type::listOf($type);
    }

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
function id(string $name = null, string $description = null)
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
function itype(string $name, string $description = null)
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
function type(string $name, string $description = null) : \Closure
{
    return function (array $fields = []) use ($name, $description) : \Closure {
        return function (callable $resolve = null) use ($name, $description, $fields) : ObjectType {
            return new ObjectType(compact('name', 'description', 'fields', 'resolve'));
        };
    };
}
