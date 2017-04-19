<?php

namespace Siler\Graphql;

use GraphQL\Lannguage\Parser;
use GraphQL\Language\AST\DocumentNode;
use Siler\Graphql;
use function Siler\array_get;

class SubscriptionManager
{
    protected $schema;
    protected $subscriptions;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->subscriptions = [];
    }

    public function handleInit(ConnectionInterface $conn)
    {
        $response = [
            'type' => Graphql\INIT_SUCCESS
        ];

        $conn->send(json_encode($response));
    }

    public function handleSubscriptionStart(ConnectionInterface $conn, $data)
    {
        $document = Parser::parse($data->query);
        $subscriptionName = $this->getSubscriptionName($document);

        if (empty($this->subscriptions[$subscriptionName])) {
            $this->subscriptions[$subscriptionName] = new Subscription(
                $subscriptionName,
                $data->query
            );
        }

        $subscriber = new Subscriber($data->id, $conn);
        $this->subscriptions->subscribe($subscriber)

        $response = [
            'type' => Graphql\SUBSCRIPTION_SUCCESS,
            'id' => $data->id,
        ];

        $conn->send(json_encode($response));
    }

    public function handleSubscriptionData(ConnectionInterface $conn, $data)
    {
        $subscriptionName = $data->subscription;

        $subscription = array_get($this->subscriptions, $subscriptionName);

        if (is_null($subscription)) {
            return;
        }

        $result = \GraphQL\GraphQL::execute(
            $this->schema,
            $subscription->query,
            $data->payload
        );

        $response = [
            'type' => Graphql\SUBSCRIPTION_DATA,
            'payload' => $result
        ];

        $subscription->broadcast($response);
    }

    public function handleSubscriptionEnd(ConnectionInterface $conn)
    {
    }

    public function getSubscriptionName(DocumentNode $document)
    {
        return $document->definitions[0]
                        ->selectionSet
                        ->selections[0]
                        ->name
                        ->value;
    }
}
