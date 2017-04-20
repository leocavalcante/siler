<?php

namespace Siler\Graphql;

class Subscription
{
    public $name;
    public $query;
    public $subscribers;

    public function __construct($name, $query)
    {
        $this->name = $name;
        $this->query = $query;
        $this->subscribers = [];
    }

    public function subscribe(Subscriber $subscriber)
    {
        $this->subscribers[$subscriber->uid] = $subscriber;
    }

    public function unsubscribe(Subscriber $subscriber)
    {
        unset($this->subscribers[$subscriber->uid]);
    }

    public function broadcast($message)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->emit($message);
        }
    }
}
