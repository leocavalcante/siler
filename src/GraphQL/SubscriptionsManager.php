<?php declare(strict_types=1);

namespace Siler\GraphQL;

use Exception;
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
    /**  @var array<string, array> */
    protected $subscriptions;
    /** @var array */
    protected $connStorage;

    /**
     * SubscriptionsManager constructor.
     *
     * @template RootValue
     * @template Context
     *
     * @param Schema $schema
     * @param array $filters
     * @param RootValue $rootValue
     * @param Context $context
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

    /**
     * @param SubscriptionsConnection $conn
     * @param array<string, mixed> $message
     * @throws Exception
     */
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
     * @param array<string, mixed>|null $message
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
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public function handleStart(SubscriptionsConnection $conn, array $data): void
    {
        try {
            /** @var array<string, mixed> $payload */
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
     * @return array
     */
    private function execute(string $query, $payload = null, ?array $variables = null): array
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
     * @param array<string, mixed> $data
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

        /** @var array<string, mixed> $subscription */
        foreach ($subscriptions as $subscription) {
            try {
                /** @var array $payload */
                $payload = array_get($data, 'payload');
                /** @var array<string, mixed> $subscription_payload */
                $subscription_payload = array_get($subscription, 'payload');
                /** @var string $query */
                $query = array_get($subscription_payload, 'query');
                /** @var array $variables */
                $variables = array_get($subscription_payload, 'variables');
                /** @var string $subscription_name */
                $subscription_name = array_get($subscription, 'name');

                if (isset($this->filters[$subscription_name])) {
                    /** @var mixed $filter */
                    $filter = $this->filters[$subscription_name];
                    if (is_callable($filter) && !$filter($payload, $variables, $this->context)) {
                        continue;
                    }
                }

                $result = $this->execute($query, $payload, $variables);

                $response = [
                    'type' => GQL_DATA,
                    'id' => $subscription['id'],
                    'payload' => $result
                ];

                /** @var SubscriptionsConnection $conn */
                $conn = $subscription['conn'];
                $conn->send(Json\encode($response));
            } catch (Exception $e) {
                $response = [
                    'type' => GQL_ERROR,
                    'id' => $subscription['id'],
                    'payload' => $e->getMessage()
                ];

                /** @var SubscriptionsConnection $conn */
                $conn = $subscription['conn'];
                $conn->send(Json\encode($response));
            } //end try
        } //end foreach
    }

    /**
     * @param SubscriptionsConnection $conn
     * @param array<string, mixed> $data
     * @return void
     */
    public function handleStop(SubscriptionsConnection $conn, array $data)
    {
        /** @var array<string, mixed> $connSubscriptions */
        $connSubscriptions = $this->connStorage[$conn->key()];
        /** @var array|null $subscription */
        $subscription = array_get($connSubscriptions, strval($data['id']));

        if (!is_null($subscription)) {
            /** @var string subscription_name */
            $subscription_name = $subscription['name'];
            /** @var int|string $subscription_index */
            $subscription_index = $subscription['index'];
            /** @var int|string $subscription_id */
            $subscription_id = $subscription['id'];
            unset($this->subscriptions[$subscription_name][$subscription_index]);
            unset($connSubscriptions[$subscription_id]);
            $this->connStorage[$conn->key()] = $connSubscriptions;
            $this->callListener(ON_DISCONNECT, [$subscription, $this->rootValue, $this->context]);
        }
    }

    /**
     * @param string $eventName
     * @param array $withArgs
     * @psalm-param array<int, mixed> $withArgs
     *
     * @return mixed|null
     */
    private function callListener(string $eventName, array $withArgs)
    {
        /** @var callable|mixed $listener */
        $listener = Container\get($eventName);

        if (is_callable($listener)) {
            return call_user_func_array($listener, $withArgs);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConnStorage(): array
    {
        return $this->connStorage;
    }

    /**
     * @return array<string, array>
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
