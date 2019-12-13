<?php

declare(strict_types=1);

namespace Siler\GraphQL;

use Closure;
use Exception;
use GraphQL\Error\Debug;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Server\IoServer;
use Siler\Container;
use Siler\Diactoros;
use Siler\Http\Request;
use Siler\Http\Response;
use UnexpectedValueException;
use WebSocket\BadOpcodeException;
use WebSocket\Client;
use Zend\Diactoros\Response\JsonResponse;
use function Siler\array_get;
use function Siler\Encoder\Json\decode;
use function Siler\Encoder\Json\encode;
use function Siler\Ratchet\graphql_subscriptions;

// Protocol messages.
// @see https://github.com/apollographql/subscriptions-transport-ws/blob/master/src/message-types.ts
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
const WEBSOCKET_SUB_PROTOCOL = 'graphql-ws';

/**
 * Sets GraphQL debug level.
 *
 * @param int $level GraphQL debug level
 * @see https://webonyx.github.io/graphql-php/error-handling
 */
function debug(int $level = Debug::INCLUDE_DEBUG_MESSAGE): void
{
    Container\set(GRAPHQL_DEBUG, $level);
}

function debugging(): int
{
    return intval(Container\get(GRAPHQL_DEBUG, 0));
}

/**
 * Initializes a new GraphQL endpoint.
 *
 * @param Schema $schema The application root Schema
 * @param mixed $rootValue Some optional GraphQL root value
 * @param mixed $context Some optional GraphQL context
 * @param string $input JSON file input, for testing
 * @throws Exception
 */
function init(Schema $schema, $rootValue = null, $context = null, string $input = 'php://input'): void
{
    $result = execute($schema, input($input), $rootValue, $context);
    Response\json($result);
}

/**
 * Retrieves the GraphQL input from SAPI.
 *
 * @param string $input
 * @return array<string, mixed>
 * @throws Exception
 */
function input(string $input = 'php://input'): array
{
    /** @var string|null $contentType */
    $contentType = Request\header('Content-Type');

    if ($contentType !== null && preg_match('#application/json(;charset=utf-8)?#', $contentType)) {
        $data = Request\json($input);
    } else {
        $data = Request\post();
    }

    if (!is_array($data) || !array_key_exists('query', $data)) {
        throw new UnexpectedValueException('Input should be a JSON object with a query field');
    }

    /** @var array<string, mixed> */
    return $data;
}

/**
 * Executes a GraphQL query over a schema.
 *
 * @param Schema $schema The application root Schema
 * @param array<string, mixed> $input Incoming query, operation and variables
 * @param mixed $rootValue Some optional GraphQL root value
 * @param mixed $context Some optional GraphQL context
 *
 * @return array
 */
function execute(Schema $schema, array $input, $rootValue = null, $context = null)
{
    /** @var string $query */
    $query = array_get($input, 'query');
    /** @var string $operation */
    $operation = array_get($input, 'operationName');
    /** @var array $variables */
    $variables = array_get($input, 'variables');

    return GraphQL::executeQuery($schema, $query, $rootValue, $context, $variables, $operation)->toArray(debugging());
}

/**
 * Same as execute(), but allows passing a custom Promise adapter.
 *
 * @param PromiseAdapter $adapter
 * @param Schema $schema
 * @param array<string, mixed> $input
 * @param null $rootValue
 * @param null $context
 *
 * @return Promise
 */
function promise_execute(PromiseAdapter $adapter, Schema $schema, array $input, $rootValue = null, $context = null): Promise
{
    /** @var string $query */
    $query = array_get($input, 'query');
    /** @var string $operation */
    $operation = array_get($input, 'operationName');
    /** @var array $variables */
    $variables = array_get($input, 'variables');

    return GraphQL::promiseToExecute($adapter, $schema, $query, $rootValue, $context, $variables, $operation);
}

/**
 * Returns a PSR-7 complaint ServerRequestInterface handler.
 *
 * @param Schema $schema GraphQL schema to execute
 *
 * @return Closure ServerRequestInterface -> IO
 *
 * @return Closure(ServerRequestInterface): JsonResponse
 */
function psr7(Schema $schema): Closure
{
    return function (ServerRequestInterface $request) use ($schema): JsonResponse {
        /** @var array<string, mixed> $input */
        $input = decode($request->getBody()->getContents());
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
    Executor::setDefaultFieldResolver(
        /**
        * @param mixed $source
        * @param mixed $context
        */
        static function ($source, array $args, $context, ResolveInfo $info) use ($resolvers) {
            /** @var string|null $fieldName */
            $fieldName = $info->fieldName;

            if ($fieldName === null) {
                throw new UnexpectedValueException('Could not get $fieldName from ResolveInfo');
            }

            /** @var ObjectType|null $parentType */
            $parentType = $info->parentType;

            if ($parentType === null) {
                throw new UnexpectedValueException('Could not get $parentType from ResolveInfo');
            }

            $parentTypeName = $parentType->name;

            if (isset($resolvers[$parentTypeName])) {
                /** @var array|object $resolver */
                $resolver = $resolvers[$parentTypeName];

                if (is_array($resolver)) {
                    if (array_key_exists($fieldName, $resolver)) {
                        /** @var callable|mixed $value */
                        $value = $resolver[$fieldName];
                        return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                    }
                }

                if (is_object($resolver)) {
                    if (isset($resolver->{$fieldName})) {
                        /** @var callable|mixed $value */
                        $value = $resolver->{$fieldName};
                        return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                    }
                }
            }

            return Executor::defaultFieldResolver($source, $args, $context, $info);
        }
    );
}

/**
 * Returns a GraphQL Subscriptions Manager.
 *
 * @param Schema $schema
 * @param array $filters
 * @param array $rootValue
 * @param array $context
 *
 * @return SubscriptionsManager
 */
function subscriptions_manager(
    Schema $schema,
    array $filters = [],
    $rootValue = [],
    $context = []
): SubscriptionsManager {
    return new SubscriptionsManager($schema, $filters, $rootValue, $context);
}

/**
 * @param Schema $schema
 * @param array $filters
 * @param string $host
 * @param int $port
 * @param array $rootValue
 * @param array $context
 *
 * @return IoServer
 * @deprecated Returns a new websocket server bootstrapped for GraphQL.
 *
 */
function subscriptions(
    Schema $schema,
    array $filters = [],
    string $host = '0.0.0.0',
    int $port = 5000,
    array $rootValue = [],
    array $context = []
): IoServer {
    $manager = subscriptions_manager($schema, $filters, $rootValue, $context);
    return graphql_subscriptions($manager, $port, $host);
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
 * @throws BadOpcodeException
 */
function publish(string $subscriptionName, $payload = null): void
{
    $message = [
        'type' => GQL_DATA,
        'subscription' => $subscriptionName,
        'payload' => $payload
    ];

    /** @var string $wsEndpoint */
    $wsEndpoint = Container\get('graphql_subscriptions_endpoint');

    $client = new Client($wsEndpoint);
    $client->send(encode($message));
}

function listen(string $eventName, callable $listener): void
{
    Container\set($eventName, $listener);
}
