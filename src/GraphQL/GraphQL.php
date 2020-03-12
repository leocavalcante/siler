<?php

declare(strict_types=1);

namespace Siler\GraphQL;

use Closure;
use Exception;
use GraphQL\Error\Debug;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\GraphQL;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Server\IoServer;
use Siler\Container;
use Siler\Diactoros;
use Siler\Http\Request;
use Siler\Http\Response;
use UnexpectedValueException;
use WebSocket\BadOpcodeException;
use WebSocket\Client;
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

/**
 * @return int
 */
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
    /** @var string|null $content_type */
    $content_type = Request\header('Content-Type');

    if ($content_type !== null && preg_match('#application/json(;charset=utf-8)?#', $content_type)) {
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
 * @template RootValue
 * @template Context
 * @param Schema $schema The application root Schema
 * @param array<string, mixed> $input Incoming query, operation and variables
 * @param mixed $rootValue
 * @psalm-param RootValue|null $rootValue
 * @param mixed $context
 * @psalm-param Context|null $context
 *
 * @return array
 */
function execute(Schema $schema, array $input, $rootValue = null, $context = null)
{
    $promise_adapter = new SyncPromiseAdapter();
    $promise = promise_execute($promise_adapter, $schema, $input, $rootValue, $context);
    return $promise_adapter->wait($promise)->toArray(debugging());
}

/**
 * Same as execute(), but allows passing a custom Promise adapter.
 *
 * @template RootValue
 * @template Context
 * @param PromiseAdapter $adapter
 * @param Schema $schema
 * @param array<string, mixed> $input
 * @param mixed $rootValue
 * @psalm-param RootValue|null $rootValue
 * @param mixed $context
 * @psalm-param Context|null $context
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
 * @return Closure(ServerRequestInterface):JsonResponse
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
 * @return void
 */
function resolvers(array $resolvers)
{
    /**
     * @param mixed $source
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return callable|mixed|null
     */
    $resolver = static function ($source, array $args, $context, ResolveInfo $info) use ($resolvers) {
        /** @var string|null $field_name */
        $field_name = $info->fieldName;

        if ($field_name === null) {
            throw new UnexpectedValueException('Could not get fieldName from ResolveInfo');
        }

        /** @var ObjectType|null $parent_type */
        $parent_type = $info->parentType;

            if ($parent_type === null) {
                throw new UnexpectedValueException('Could not get parentType from ResolveInfo');
            }

        $parent_type_name = $parent_type->name;

        if (isset($resolvers[$parent_type_name])) {
            /** @var array|object $resolver */
            $resolver = $resolvers[$parent_type_name];
            $value = null;

            if (is_array($resolver)) {
                if (array_key_exists($field_name, $resolver)) {
                    /** @var callable|mixed $value */
                    $value = $resolver[$field_name];
                }
            }

            if (is_object($resolver)) {
                if (isset($resolver->{$field_name})) {
                    /** @var callable|mixed $value */
                    $value = $resolver->{$field_name};
                }
            }

            if (is_callable($value)) {
                return $value($source, $args, $context, $info);
            }

            if ($value !== null) {
                return $value;
            }
        }

        return Executor::defaultFieldResolver($source, $args, $context, $info);
    };

    Executor::setDefaultFieldResolver(
        static function ($source, array $args, $context, ResolveInfo $info) use ($resolvers, $resolver) {
            $field_node = $info->fieldNodes[0];
            /** @var NodeList $directives */
            $directives = $field_node->directives;

            if (!empty($directives)) {
                /** @var DirectiveNode $directive */
                foreach ($directives as $directive) {
                    $directive_name = "@{$directive->name->value}";

                    if (array_key_exists($directive_name, $resolvers)) {
                        $resolver = $resolvers[$directive_name]($resolver);
                    }
                }
            }

            return $resolver($source, $args, $context, $info);
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
 * @return IoServer
 * @deprecated Returns a new websocket server bootstrapped for GraphQL.
 * @noinspection PhpTooManyParametersInspection
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
 * @return void
 */
function subscriptions_at(string $url): void
{
    Container\set('graphql_subscriptions_endpoint', $url);
}

/**
 * Publishes the given $payload to the $subscribeName.
 *
 * @param string $subscriptionName
 * @param mixed $payload
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

    /** @var string $ws_endpoint */
    $ws_endpoint = Container\get('graphql_subscriptions_endpoint');

    $opts = [
        'headers' => [
            'Sec-WebSocket-Protocol' => WEBSOCKET_SUB_PROTOCOL
        ]
    ];

    $client = new Client($ws_endpoint, $opts);
    $client->send(encode($message));
}

/**
 * @param string $eventName
 * @param callable $listener
 */
function listen(string $eventName, callable $listener): void
{
    Container\set($eventName, $listener);
}
