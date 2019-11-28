<?php

declare(strict_types=1);

namespace Siler\GraphQL;

use Exception;
use GraphQL\Executor\Promise\Promise;
use GraphQL\GraphQL;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use Siler\Container;
use Siler\Encoder\Json;
use function Siler\array_get;

/**
 * Class SubscriptionsManager
 *
 * @package Siler\GraphQL
 */
class SubscriptionsManager
{
    /** @var Schema */
    protected $schema;
    /** @var array */
    protected $filters;
    /** @var mixed */
    protected $rootValue;
    /** @var mixed */
    protected $context;
    /**  @var array<array> */
    protected $subscriptions;
    /** @var array */
    protected $connStorage;

    /**
     * SubscriptionsManager constructor.
     *
     * @param Schema $schema
     * @param array $filters
     * @param mixed $rootValue
     * @param mixed $context
     */
    public function __construct(Schema $schema, array $filters = [], $rootValue = [], $context = [])
    {
        $this->schema = $schema;
        $this->filters = $filters;
        $this->rootValue = $rootValue;
        $this->context = $context;
        $this->subscriptions = [];
        $this->connStorage = [];
    }

    public function handle(SubscriptionsConnection $conn, array $message): void
    {
        switch ($message['type']) {
            case GQL_CONNECTION_INIT:
                $this->handleConnectionInit($conn, $message);
                break;

            case GQL_START:
                $this->handleStart($conn, $message);
                break;

            case GQL_DATA:
                $this->handleData($message);
                break;

            case GQL_STOP:
                $this->handleStop($conn, $message);
                break;
        }
    }

    /**
     * @param SubscriptionsConnection $conn
     * @param array|null $message
     * @throws Exception
     */
    public function handleConnectionInit(SubscriptionsConnection $conn, ?array $message = null): void
    {
        try {
            $this->connStorage[$conn->key()] = [];

            $response = [
                'type' => GQL_CONNECTION_ACK,
                'payload' => []
            ];

            /** @var array|mixed $context */
            $context = $this->callListener(ON_CONNECT, [array_get($message, 'payload', []), $this->context]);

            if (is_array($context) && is_array($this->context)) {
                $this->context = array_merge($this->context, $context);
            }
        } catch (Exception $e) {
            $response = [
                'type' => GQL_CONNECTION_ERROR,
                'payload' => $e->getMessage()
            ];
        } finally {
            $conn->send(Json\encode($response));
        }
    }


    /**
     * @param SubscriptionsConnection $conn
     * @param array $data
     * @throws Exception
     */
    public function handleStart(SubscriptionsConnection $conn, array $data): void
    {
        try {
            /** @var array $payload */
            $payload = array_get($data, 'payload');
            /** @var string|null $query */
            $query = array_get($payload, 'query');

            if ($query === null) {
                throw new Exception('Missing query parameter from payload');
            }

            $document = Parser::parse($query);
            /** @var OperationDefinitionNode $definition */
            $definition = $document->definitions[0];
            $operation = $definition->operation;

            if ($operation === 'subscription') {
                $data['name'] = $this->getSubscriptionName($document);
                $data['conn'] = $conn;

                $this->subscriptions[$data['name']][] = $data;
                end($this->subscriptions[$data['name']]);
                $data['index'] = key($this->subscriptions[$data['name']]);

                /** @var array $connSubscriptions */
                $connSubscriptions = array_key_exists($conn->key(), $this->connStorage)
                    ? $this->connStorage[$conn->key()]
                    : [];
                $connSubscriptions[strval($data['id'])] = $data;
                $this->connStorage[$conn->key()] = $connSubscriptions;

                $this->callListener(ON_OPERATION, [$data, $this->rootValue, $this->context]);
            } else {
                /** @var array $variables */
                $variables = array_get($payload, 'variables');
                $result = $this->execute($query, $payload, $variables);

                $response = [
                    'type' => GQL_DATA,
                    'id' => $data['id'],
                    'payload' => $result
                ];

                $conn->send(Json\encode($response));

                $response = [
                    'type' => GQL_COMPLETE,
                    'id' => $data['id']
                ];

                $conn->send(Json\encode($response));

                $this->callListener(ON_OPERATION_COMPLETE, [$data, $this->rootValue, $this->context]);
            }
        } catch (Exception $e) {
            $response = [
                'type' => GQL_ERROR,
                'id' => $data['id'],
                'payload' => $e->getMessage()
            ];

            $conn->send(Json\encode($response));

            $response = [
                'type' => GQL_COMPLETE,
                'id' => $data['id']
            ];

            $conn->send(Json\encode($response));
        } //end try
    }

    /**
     * @param string $query
     * @param mixed $payload
     * @param array|null $variables
     *
     * @return array|Promise
     */
    private function execute(string $query, $payload = null, ?array $variables = null)
    {
        return GraphQL::executeQuery($this->schema, $query, $payload, $this->context, $variables)->toArray(debugging());
    }

    public function getSubscriptionName(DocumentNode $document): string
    {
        /** @var OperationDefinitionNode $definition */
        $definition = $document->definitions[0];
        /** @var FieldNode $node */
        $node = $definition->selectionSet->selections[0];

        return $node->name->value;
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    public function handleData(array $data)
    {
        /** @var string $subscriptionName */
        $subscriptionName = $data['subscription'];
        /** @var array<array>|null $subscriptions */
        $subscriptions = array_get($this->subscriptions, $subscriptionName);

        if (is_null($subscriptions)) {
            return;
        }

        /** @var array $subscription */
        foreach ($subscriptions as $subscription) {
            try {
                /** @var array $payload */
                $payload = array_get($data, 'payload');
                /** @var string $query */
                $query = array_get($subscription['payload'], 'query');
                /** @var array $variables */
                $variables = array_get($subscription['payload'], 'variables');

                if (isset($this->filters[$subscription['name']])) {
                    if (!$this->filters[$subscription['name']]($payload, $variables, $this->context)) {
                        continue;
                    }
                }

                $result = $this->execute($query, $payload, $variables);

                $response = [
                    'type' => GQL_DATA,
                    'id' => $subscription['id'],
                    'payload' => $result
                ];

                /** @noinspection PhpUndefinedMethodInspection */
                $subscription['conn']->send(Json\encode($response));
            } catch (Exception $e) {
                $response = [
                    'type' => GQL_ERROR,
                    'id' => $subscription['id'],
                    'payload' => $e->getMessage()
                ];

                /** @noinspection PhpUndefinedMethodInspection */
                $subscription['conn']->send(Json\encode($response));
            } //end try
        } //end foreach
    }

    /**
     * @param SubscriptionsConnection $conn
     * @param array $data
     *
     * @return void
     */
    public function handleStop(SubscriptionsConnection $conn, array $data)
    {
        $connSubscriptions = $this->connStorage[$conn->key()];
        $subscription = array_get($connSubscriptions, $data['id']);

        if (!is_null($subscription)) {
            unset($this->subscriptions[$subscription['name']][$subscription['index']]);
            unset($connSubscriptions[$subscription['id']]);
            $this->connStorage[$conn->key()] = $connSubscriptions;
            $this->callListener(ON_DISCONNECT, [$subscription, $this->rootValue, $this->context]);
        }
    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    public function getConnStorage(): array
    {
        return $this->connStorage;
    }

    /**
     * @param string $eventName
     * @param array $withArgs
     *
     * @return mixed|null
     */
    private function callListener(string $eventName, array $withArgs)
    {
        $listener = Container\get($eventName);

        if (is_callable($listener)) {
            return call_user_func_array($listener, $withArgs);
        }

        return null;
    }
}
