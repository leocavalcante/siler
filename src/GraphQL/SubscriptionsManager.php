<?php

declare(strict_types=1);

namespace Siler\GraphQL;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use Ratchet\ConnectionInterface;
use Siler\Container;
use function Siler\array_get;

/**
 * Class SubscriptionsManager
 *
 * @package Siler\GraphQL
 */
class SubscriptionsManager
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $rootValue;

    /**
     * @var array
     */
    protected $context;

    /**
     * @var array
     */
    protected $subscriptions;

    /**
     * @var \SplObjectStorage
     */
    protected $connStorage;


    /**
     * SubscriptionsManager constructor.
     *
     * @param Schema $schema
     * @param array  $filters
     * @param array  $rootValue
     * @param array  $context
     */
    public function __construct(Schema $schema, array $filters = [], array $rootValue = [], array $context = [])
    {
        $this->schema        = $schema;
        $this->filters       = $filters;
        $this->rootValue     = $rootValue;
        $this->context       = $context;
        $this->subscriptions = [];
        $this->connStorage   = new \SplObjectStorage();
    }


    /**
     * @param ConnectionInterface $conn
     * @param array|null          $data
     *
     * @return void
     */
    public function handleConnectionInit(ConnectionInterface $conn, ?array $data = null)
    {
        try {
            $this->connStorage->offsetSet($conn, []);

            $response = [
                'type'    => GQL_CONNECTION_ACK,
                'payload' => [],
            ];

            $context = $this->callListener(ON_CONNECT, [array_get($data, 'payload', [])]);

            if (is_array($context)) {
                $this->context = array_merge($this->context, $context);
            }
        } catch (\Exception $e) {
            $response = [
                'type'    => GQL_CONNECTION_ERROR,
                'payload' => $e->getMessage(),
            ];
        } finally {
            $result = json_encode($response);

            if (false === $result) {
                throw new \UnexpectedValueException('Could not encode response');
            }

            $conn->send($result);
        }//end try
    }


    /**
     * @param ConnectionInterface $conn
     * @param array               $data
     *
     * @return void
     */
    public function handleStart(ConnectionInterface $conn, array $data)
    {
        try {
            $payload = array_get($data, 'payload');
            $query   = array_get($payload, 'query');

            if (is_null($query)) {
                throw new \Exception('Missing query parameter from payload');
            }

            $variables = array_get($payload, 'variables');

            $document  = Parser::parse($query);
            // @phan-suppress-next-line PhanUndeclaredProperty
            $operation = $document->definitions[0]->operation;
            $result = $this->execute($query, $payload, $variables);

            $response = [
                'type'    => GQL_DATA,
                'id'      => $data['id'],
                'payload' => $result,
            ];

            $response = json_encode($response);

            if (false === $response) {
                throw new \UnexpectedValueException('Could not encode response');
            }

            $conn->send($response);

            if ($operation == 'subscription') {
                $data['name'] = $this->getSubscriptionName($document);
                $data['conn'] = $conn;

                $this->subscriptions[$data['name']][] = $data;
                end($this->subscriptions[$data['name']]);
                $data['index'] = key($this->subscriptions[$data['name']]);

                $connSubscriptions = $this->connStorage->offsetExists($conn) ? $this->connStorage->offsetGet($conn) : [];
                $connSubscriptions[$data['id']] = $data;
                $this->connStorage->offsetSet($conn, $connSubscriptions);

                $this->callListener(ON_OPERATION, [$data, $this->rootValue, $this->context]);
            } else {
                $response = [
                    'type' => GQL_COMPLETE,
                    'id'   => $data['id'],
                ];

                $response = json_encode($response);

                if (false === $response) {
                    throw new \UnexpectedValueException('Could not encode response');
                }

                $conn->send($response);
                $this->callListener(ON_OPERATION_COMPLETE, [$data, $this->rootValue, $this->context]);
            }//end if
        } catch (\Exception $e) {
            $response = [
                'type'    => GQL_ERROR,
                'id'      => $data['id'],
                'payload' => $e->getMessage(),
            ];

            $response = json_encode($response);

            if (false === $response) {
                throw new \UnexpectedValueException('Could not encode response');
            }

            $conn->send($response);

            $response = [
                'type' => GQL_COMPLETE,
                'id'   => $data['id'],
            ];

            $response = json_encode($response);

            if (false === $response) {
                throw new \UnexpectedValueException('Could not encode response');
            }

            $conn->send($response);
        }//end try
    }


    /**
     * @param array $data
     *
     * @return void
     */
    public function handleData(array $data)
    {
        $subscriptionName = $data['subscription'];
        $subscriptions    = array_get($this->subscriptions, $subscriptionName);

        if (is_null($subscriptions)) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $payload   = array_get($data, 'payload');
                $query     = array_get($subscription['payload'], 'query');
                $variables = array_get($subscription['payload'], 'variables');

                if (isset($this->filters[$subscription['name']])) {
                    if (!$this->filters[$subscription['name']]($payload, $variables, $this->context)) {
                        continue;
                    }
                }

                $result = $this->execute($query, $payload, $variables);

                $response = [
                    'type'    => GQL_DATA,
                    'id'      => $subscription['id'],
                    'payload' => $result,
                ];

                $response = json_encode($response);

                if (false === $response) {
                    throw new \UnexpectedValueException('Could not encode response');
                }

                $subscription['conn']->send($response);
            } catch (\Exception $e) {
                $response = [
                    'type'    => GQL_ERROR,
                    'id'      => $subscription['id'],
                    'payload' => $e->getMessage(),
                ];

                $response = json_encode($response);

                if (false === $response) {
                    throw new \UnexpectedValueException('Could not encode response');
                }

                $subscription['conn']->send($response);
            }//end try
        }//end foreach
    }


    /**
     * @param ConnectionInterface $conn
     * @param array               $data
     *
     * @return void
     */
    public function handleStop(ConnectionInterface $conn, array $data)
    {
        $connSubscriptions = $this->connStorage->offsetGet($conn);
        $subscription      = array_get($connSubscriptions, $data['id']);

        if (!is_null($subscription)) {
            unset($this->subscriptions[$subscription['name']][$subscription['index']]);
            unset($connSubscriptions[$subscription['id']]);
            $this->connStorage->offsetSet($conn, $connSubscriptions);
            $this->callListener(ON_DISCONNECT, [$subscription, $this->rootValue, $this->context]);
        }
    }


    /**
     * @param DocumentNode $document
     *
     * @return string
     *
     * @suppress PhanUndeclaredProperty
     */
    public function getSubscriptionName(DocumentNode $document) : string
    {
        return $document->definitions[0]
            ->selectionSet
            ->selections[0]
            ->name
            ->value;
    }


    public function getSubscriptions() : array
    {
        return $this->subscriptions;
    }


    public function getConnStorage() : \SplObjectStorage
    {
        return $this->connStorage;
    }


    /**
     * @param string     $query
     * @param mixed      $payload
     * @param array|null $variables
     *
     * @return array|\GraphQL\Executor\Promise\Promise
     */
    private function execute(string $query, $payload = null, ?array $variables = null)
    {
        return \GraphQL\GraphQL::executeQuery(
            $this->schema,
            $query,
            $payload,
            $this->context,
            $variables
        )->toArray();
    }


    /**
     * @param string $eventName
     * @param array  $withArgs
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
