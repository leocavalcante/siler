<?php

namespace Siler\Graphql;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use Ratchet\ConnectionInterface;
use function Siler\array_get;

class WsManager
{
    protected $schema;
    protected $filters;
    protected $rootValue;
    protected $context;
    protected $subscriptions;
    protected $connStorage;

    public function __construct(Schema $schema, array $filters = null, array $rootValue = null, array $context = null)
    {
        $this->schema = $schema;
        $this->filters = $filters;
        $this->rootValue = $rootValue;
        $this->context = $context;
        $this->subscriptions = [];
        $this->connStorage = new \SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return void
     *
     * @psalm-suppress PossiblyFalseArgument
     */
    public function handleConnectionInit(ConnectionInterface $conn)
    {
        try {
            $this->connStorage->offsetSet($conn, []);

            $response = [
                'type'    => GQL_CONNECTION_ACK,
                'payload' => [],
            ];
        } catch (\Exception $e) {
            $response = [
                'type'    => GQL_CONNECTION_ERROR,
                'payload' => $e->getMessage(),
            ];
        } finally {
            $conn->send(json_encode($response));
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param array               $data
     *
     * @return void
     *
     * @psalm-suppress PossiblyFalseArgument
     */
    public function handleStart(ConnectionInterface $conn, array $data)
    {
        try {
            $payload = array_get($data, 'payload');
            $query = array_get($payload, 'query');

            if (is_null($query)) {
                throw new \Exception('Missing query parameter from payload');
            }

            $variables = array_get($payload, 'variables');

            $document = Parser::parse($query);
            $operation = $document->definitions[0]->operation;
            $result = $this->execute($query, $payload, $variables);

            $response = [
                'type'    => GQL_DATA,
                'id'      => $data['id'],
                'payload' => $result,
            ];

            $conn->send(json_encode($response));

            if ($operation == 'subscription') {
                $data['name'] = $this->getSubscriptionName($document);
                $data['conn'] = $conn;

                $this->subscriptions[$data['name']][] = $data;
                end($this->subscriptions[$data['name']]);
                $data['index'] = key($this->subscriptions[$data['name']]);

                $connSubscriptions = $this->connStorage->offsetExists($conn) ? $this->connStorage->offsetGet($conn) : [];
                $connSubscriptions[$data['id']] = $data;
                $this->connStorage->offsetSet($conn, $connSubscriptions);
            } else {
                $response = [
                    'type' => GQL_COMPLETE,
                    'id'   => $data['id'],
                ];

                $conn->send(json_encode($response));
            }
        } catch (\Exception $e) {
            $response = [
                'type'    => GQL_ERROR,
                'id'      => $data['id'],
                'payload' => $e->getMessage(),
            ];

            $conn->send(json_encode($response));

            $response = [
                'type' => GQL_COMPLETE,
                'id'   => $data['id'],
            ];

            $conn->send(json_encode($response));
        }
    }

    public function handleData(array $data)
    {
        $subscriptionName = $data['subscription'];
        $subscriptions = array_get($this->subscriptions, $subscriptionName);

        if (is_null($subscriptions)) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $payload = array_get($data, 'payload');
                $query = array_get($subscription['payload'], 'query');
                $variables = array_get($subscription['payload'], 'variables');

                if (!is_null($this->filters) && isset($this->filters[$subscription['name']])) {
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

                $subscription['conn']->send(json_encode($response));
            } catch (\Exception $e) {
                $response = [
                    'type'    => GQL_ERROR,
                    'id'      => $subscription['id'],
                    'payload' => $e->getMessage(),
                ];

                $subscription['conn']->send(json_encode($response));
            }
        }
    }

    public function handleStop(ConnectionInterface $conn, array $data)
    {
        $connSubscriptions = $this->connStorage->offsetGet($conn);
        $subscription = array_get($connSubscriptions, $data['id']);

        if (!is_null($subscription)) {
            unset($this->subscriptions[$subscription['name']][$subscription['index']]);
            unset($connSubscriptions[$subscription['id']]);
            $this->connStorage->offsetSet($conn, $connSubscriptions);
        }
    }

    /**
     * @param DocumentNode $document
     *
     * @psalm-suppress NoInterfaceProperties
     */
    public function getSubscriptionName(DocumentNode $document)
    {
        return $document->definitions[0]
                        ->selectionSet
                        ->selections[0]
                        ->name
                        ->value;
    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function getConnStorage()
    {
        return $this->connStorage;
    }

    private function execute($query, $payload, $variables)
    {
        return \GraphQL\GraphQL::execute(
            $this->schema,
            $query,
            $payload,
            $this->context,
            $variables
        );
    }
}
