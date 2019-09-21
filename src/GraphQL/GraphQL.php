<?php

declare(strict_types=1);
/*
 * Helper functions for webonyx/graphql-php GraphQL implementation.
 */

namespace Siler\GraphQL;

use Closure;
use GraphQL\Error\Debug;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
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
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Client;
use Ratchet\Client\WebSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Siler\Container;
use Siler\Diactoros;
use Siler\Http\Request;
use Siler\Http\Response;
use UnexpectedValueException;
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

const ON_OPERATION = 'graphql_on_operation';
const ON_OPERATION_COMPLETE = 'graphql_on_operation_complete';
const ON_CONNECT = 'graphql_on_connect';
const ON_DISCONNECT = 'graphql_on_disconnect';

const GRAPHQL_DEBUG = 'graphql_debug';

/**
 * Sets GraphQL debug level.
 *
 * @param int $level GraphQL debug level
 * @see https://webonyx.github.io/graphql-php/error-handling
 */
function debug(int $level = Debug::INCLUDE_DEBUG_MESSAGE)
{
    Container\set(GRAPHQL_DEBUG, $level);
}

/**
 * Initializes a new GraphQL endpoint.
 *
 * @param Schema $schema The application root Schema
 * @param mixed $rootValue Some optional GraphQL root value
 * @param mixed $context Some optional GraphQL context
 * @param string $input JSON file input, for testing
 *
 * @return void
 */
function init(Schema $schema, $rootValue = null, $context = null, string $input = 'php://input')
{
    $result = execute($schema, input($input), $rootValue, $context);
    Response\json($result);
}

/**
 * Retrieves the GraphQL input from SAPI.
 *
 * @param string $input
 *
 * @return array
 */
function input(string $input = 'php://input'): array
{
    $contentType = Request\header('Content-Type');

    if (!is_null($contentType) && preg_match('#application/json(;charset=utf-8)?#', $contentType)) {
        $data = Request\json($input);
    } else {
        $data = Request\post();
    }

    if (!is_array($data)) {
        throw new UnexpectedValueException('Input should be a JSON object');
    }

    return $data;
}

/**
 * Executes a GraphQL query over a schema.
 *
 * @param Schema $schema The application root Schema
 * @param array $input Incoming query, operation and variables
 * @param mixed $rootValue Some optional GraphQL root value
 * @param mixed $context Some optional GraphQL context
 *
 * @return array
 */
function execute(Schema $schema, array $input, $rootValue = null, $context = null)
{
    $query = array_get($input, 'query');
    $operation = array_get($input, 'operationName');
    $variables = array_get($input, 'variables');

    return GraphQL::executeQuery($schema, $query, $rootValue, $context, $variables, $operation)->toArray(Container\get(GRAPHQL_DEBUG));
}

/**
 * Same as execute(), but allows passing a custom Promise adapter.
 *
 * @param PromiseAdapter $adapter
 * @param Schema $schema
 * @param array $input
 * @param null $rootValue
 * @param null $context
 *
 * @return Promise
 */
function promise_execute(PromiseAdapter $adapter, Schema $schema, array $input, $rootValue = null, $context = null): Promise
{
    $query = array_get($input, 'query');
    $operation = array_get($input, 'operationName');
    $variables = array_get($input, 'variables');

    return GraphQL::promiseToExecute($adapter, $schema, $query, $rootValue, $context, $variables, $operation);
}

/**
 * Returns a PSR-7 complaint ServerRequestInterface handler.
 *
 * @param Schema $schema GraphQL schema to execute
 *
 * @return Closure ServerRequestInterface -> IO
 */
function psr7(Schema $schema): Closure
{
    return function (ServerRequestInterface $request) use ($schema) {
        $input = json_decode((string)$request->getBody(), true);

        if (!is_array($input)) {
            throw new UnexpectedValueException('Input should be a JSON object');
        }

        $data = execute($schema, $input);

        return Diactoros\json($data);
    };
}

/**
 * Returns a new GraphQL\Schema from a file containing GraphQL language.
 * Also sets a Siler's default field resolver based on $resolvers array.
 *
 * @param string $typeDefs
 * @param array $resolvers
 * @param callable|null $typeConfigDecorator
 * @param array $options
 *
 * @return Schema
 */
function schema(string $typeDefs, array $resolvers = [], ?callable $typeConfigDecorator = null, array $options = []): Schema
{
    if (!empty($resolvers)) {
        resolvers($resolvers);
    }

    return BuildSchema::build($typeDefs, $typeConfigDecorator, $options, $resolvers);
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
    Executor::setDefaultFieldResolver(function ($source, $args, $context, ResolveInfo $info) use ($resolvers) {
        $fieldName = $info->fieldName;

        if (is_null($fieldName)) {
            throw new UnexpectedValueException('Could not get $fieldName from ResolveInfo');
        }

        if (is_null($info->parentType)) {
            throw new UnexpectedValueException('Could not get $parentType from ResolveInfo');
        }

        $parentTypeName = $info->parentType->name;

        if (isset($resolvers[$parentTypeName])) {
            $resolver = $resolvers[$parentTypeName];

            if (is_array($resolver)) {
                if (array_key_exists($fieldName, $resolver)) {
                    $value = $resolver[$fieldName];

                    return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                }
            }

            if (is_object($resolver)) {
                if (isset($resolver->{$fieldName})) {
                    $value = $resolver->{$fieldName};

                    return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                }
            }
        }

        return Executor::defaultFieldResolver($source, $args, $context, $info);
    });
}

/**
 * Returns a new websocket server bootstrapped for GraphQL.
 *
 * @param Schema $schema
 * @param array $filters
 * @param string $host
 * @param int $port
 * @param array $rootValue
 * @param array $context
 *
 * @return IoServer
 */
function subscriptions(
    Schema $schema,
    array $filters = [],
    string $host = '0.0.0.0',
    int $port = 5000,
    array $rootValue = [],
    array $context = []
): IoServer {
    $manager = new SubscriptionsManager($schema, $filters, $rootValue, $context);
    $server = new SubscriptionsServer($manager);
    $websocket = new WsServer($server);
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
function subscriptions_at(string $url)
{
    Container\set('graphql_subscriptions_endpoint', $url);
}

/**
 * Publishes the given $payload to the $subscribeName.
 *
 * @param string $subscriptionName
 * @param mixed $payload
 *
 * @return void
 */
function publish(string $subscriptionName, $payload = null)
{
    $wsEndpoint = Container\get('graphql_subscriptions_endpoint');

    Client\connect($wsEndpoint, ['graphql-ws'])->then(function (WebSocket $conn) use ($subscriptionName, $payload) {
        $request = [
            'type' => GQL_DATA,
            'subscription' => $subscriptionName,
            'payload' => $payload
        ];

        $conn->send(json_encode($request));
        $conn->close();
    });
}

function listen(string $eventName, callable $listener)
{
    Container\set($eventName, $listener);
}

/**
 * Returns a GraphQL value definition.
 *
 * @param string $name
 * @param string|null $description
 * @return Closure -> value -> array
 */
function val(string $name, ?string $description = null): Closure
{
    return function ($value) use ($name, $description): array {
        return [
            'name' => $name,
            'description' => $description,
            'value' => $value
        ];
    };
}

/**
 *  Returns a GraphQL Enum type.
 *
 * @param string $name
 * @param string|null $description
 * @return Closure -> values -> EnumType
 */
function enum(string $name, ?string $description = null): Closure
{
    return function (array $values) use ($name, $description): EnumType {
        return new EnumType(['name' => $name, 'description' => $description, 'values' => $values]);
    };
}

/**
 * Returns an evaluable field definition.
 *
 * @param Type $type
 * @param string $name
 * @param string|null $description
 * @return Closure -> (resolve, args) -> array
 */
function field(Type $type, string $name, ?string $description = null): Closure
{
    return function ($resolve = null, array $args = null) use ($type, $name, $description) {
        if (is_string($resolve)) {
            $resolve = function () use ($resolve) {
                return new $resolve();
            };
        }

        return [
            'type' => $type,
            'name' => $name,
            'description' => $description,
            'resolve' => $resolve,
            'args' => $args
        ];
    };
}

/**
 * Returns an evaluable String field definition.
 *
 * @param string|null $name
 * @param string|null $description
 * @return StringType|Closure -> (resolve, args) -> array
 */
function str(?string $name = null, ?string $description = null)
{
    if (is_null($name)) {
        return Type::string();
    }

    return field(Type::string(), $name, $description);
}

/**
 * Returns an evaluable Integer field definition.
 *
 * @param string|null $name
 * @param string|null $description
 * @return IntType|Closure -> (resolve, args) -> array
 */
function int(?string $name = null, ?string $description = null)
{
    if (is_null($name)) {
        return Type::int();
    }

    return field(Type::int(), $name, $description);
}

/**
 * Returns an evaluable Float field definition.
 *
 * @param string|null $name
 * @param string|null $description
 * @return FloatType|Closure -> (resolve, args) -> array
 */
function float(?string $name = null, ?string $description = null)
{
    if (is_null($name)) {
        return Type::float();
    }

    return field(Type::float(), $name, $description);
}

/**
 * Returns an evaluable Boolean field definition.
 *
 * @param string|null $name
 * @param string|null $description
 * @return BooleanType|Closure -> (resolve, args) -> array
 */
function bool(?string $name = null, ?string $description = null)
{
    if (is_null($name)) {
        return Type::boolean();
    }

    return field(Type::boolean(), $name, $description);
}

/**
 * @param Type $type
 * @param string|null $name
 * @param string|null $description
 * @return ListOfType|Closure -> (resolve, args) -> array
 */
function list_of(Type $type, ?string $name = null, ?string $description = null)
{
    if (is_null($name)) {
        return Type::listOf($type);
    }

    return field(Type::listOf($type), $name, $description);
}

/**
 * Returns an evaluable Id field definition.
 *
 * @param string|null $name
 * @param string|null $description
 * @return IDType|Closure -> (resolve, args) -> array
 */
function id(?string $name = null, ?string $description = null)
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
 * @param string|null $description
 * @return Closure -> fields -> resolve -> InterfaceType
 */
function itype(string $name, ?string $description = null)
{
    return function (array $fields = []) use ($name, $description) {
        return function (callable $resolveType) use ($name, $description, $fields) {
            return new InterfaceType([
                'name' => $name,
                'description' => $description,
                'fields' => $fields,
                'resolveType' => $resolveType
            ]);
        };
    };
}

/**
 * Returns an ObjectType factory function.
 *
 * @param string $name
 * @param string|null $description
 * @return Closure -> fields -> resolve -> ObjectType
 */
function type(string $name, ?string $description = null): Closure
{
    return function (array $fields = []) use ($name, $description): Closure {
        return function (callable $resolve = null) use ($name, $description, $fields): ObjectType {
            return new ObjectType([
                'name' => $name,
                'description' => $description,
                'fields' => $fields,
                'resolve' => $resolve
            ]);
        };
    };
}
