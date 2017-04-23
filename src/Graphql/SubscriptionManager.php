<?php

namespace Siler\Graphql;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Schema;
use Ratchet\ConnectionInterface;
use Siler\Graphql;
use function Siler\array_get;

class SubscriptionManager
{
    protected $schema;
    protected $filters;
    protected $rootValue;
    protected $context;
    protected $subscriptions;
    protected $connSubStorage;

    public function __construct(Schema $schema, array $filters = null, array $rootValue = null, array $context = null)
    {
        $this->schema = $schema;
        $this->filters = $filters;
        $this->rootValue = $rootValue;
        $this->context = $context;
        $this->subscriptions = [];
        $this->connSubStorage = new \SplObjectStorage();
    }

    public function handleInit(ConnectionInterface $conn)
    {
        $this->connSubStorage->offsetSet($conn, []);

        $response = [
            'type' => Graphql\INIT_SUCCESS,
        ];

        $conn->send(json_encode($response));
    }

    public function handleSubscriptionStart(ConnectionInterface $conn, array $subscription)
    {
        try {
            $document = Parser::parse($subscription['query']);
            $subscription['name'] = $this->getSubscriptionName($document);
            $subscription['conn'] = $conn;

            $this->subscriptions[$subscription['name']][] = $subscription;
            end($this->subscriptions[$subscription['name']]);
            $subscription['index'] = key($this->subscriptions[$subscription['name']]);

            $connSubscriptions = $this->connSubStorage->offsetGet($conn);
            $connSubscriptions[$subscription['id']] = $subscription;
            $this->connSubStorage->offsetSet($conn, $connSubscriptions);

            $response = [
                'type' => Graphql\SUBSCRIPTION_SUCCESS,
                'id'   => $subscription['id'],
            ];

            $conn->send(json_encode($response));
        } catch (\Exception $exception) {
            $response = [
                'type'    => Graphql\SUBSCRIPTION_FAIL,
                'id'      => $subscription['id'],
                'payload' => [
                    'errors' => [
                        ['message' => $exception->getMessage()],
                    ],
                ],
            ];

            $conn->send(json_encode($response));
        }
    }

    public function handleSubscriptionData(array $data)
    {
        $subscriptionName = $data['subscription'];
        $subscriptions = array_get($this->subscriptions, $subscriptionName);

        if (is_null($subscriptions)) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            $payload = array_get($data, 'payload');
            $variables = array_get($subscription, 'variables');

            if (!is_null($this->filters) && isset($this->filters[$subscription['name']])) {
                if (!$this->filters[$subscription['name']]($payload, $variables, $this->context)) {
                    continue;
                }
            }

            $result = \GraphQL\GraphQL::execute(
                $this->schema,
                $subscription['query'],
                $payload,
                $this->context,
                $variables
            );

            $response = [
                'type'    => Graphql\SUBSCRIPTION_DATA,
                'payload' => $result,
                'id'      => $subscription['id'],
            ];

            $subscription['conn']->send(json_encode($response));
        }
    }

    public function handleSubscriptionEnd(ConnectionInterface $conn, array $data)
    {
        $connSubscriptions = $this->connSubStorage->offsetGet($conn);
        $subscription = array_get($connSubscriptions, $data['id']);

        if (!is_null($subscription)) {
            unset($this->subscriptions[$subscription['name']][$subscription['index']]);
            unset($connSubscriptions[$subscription['id']]);
            $this->connSubStorage->offsetSet($conn, $connSubscriptions);
        }
    }

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

    public function getConnSubStorage()
    {
        return $this->connSubStorage;
    }
}
