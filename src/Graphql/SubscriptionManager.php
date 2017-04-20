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
    protected $subscriptions;
    protected $subscribers;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->subscriptions = [];
        $this->subscribers = new \SplObjectStorage();
    }

    public function handleInit(ConnectionInterface $conn)
    {
        $response = [
            'type' => Graphql\INIT_SUCCESS,
        ];

        $conn->send(json_encode($response));
    }

    public function handleSubscriptionStart(ConnectionInterface $conn, $data)
    {
        try {
            $document = Parser::parse($data->query);
            $subscriptionName = $this->getSubscriptionName($document);

            if (empty($this->subscriptions[$subscriptionName])) {
                $this->subscriptions[$subscriptionName] = new Subscription(
                    $subscriptionName,
                    $data->query
                );
            }

            $subscriber = new Subscriber(uniqid(), $data->id, $conn);
            $subscriber->subscribe($this->subscriptions[$subscriptionName]);

            if (!$this->subscribers->contains($conn)) {
                $this->subscribers->attach($conn, []);
            }

            $connSubscribers = $this->subscribers->offsetGet($conn);
            $connSubscribers[$subscriber->id] = $subscriber;
            $this->subscribers->offsetSet($conn, $connSubscribers);

            $response = [
                'type' => Graphql\SUBSCRIPTION_SUCCESS,
                'id'   => $data->id,
            ];
        } catch (\Exception $exception) {
            $response = [
                'type'    => Graphql\SUBSCRIPTION_FAIL,
                'id'      => $data->id,
                'payload' => $exception->getMessage(),
            ];
        } finally {
            $conn->send(json_encode($response));
        }
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
            (array) $data->payload
        );

        $response = [
            'type'    => Graphql\SUBSCRIPTION_DATA,
            'payload' => $result,
        ];

        $subscription->broadcast($response);
    }

    public function handleSubscriptionEnd(ConnectionInterface $conn, $data)
    {
        $subscribers = $this->subscribers->offsetGet($conn);
        $subscriber = $subscribers[$data->id];
        $subscriber->unsubscribe();
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
