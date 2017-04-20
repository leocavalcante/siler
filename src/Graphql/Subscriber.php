<?php

namespace Siler\Graphql;

use Ratchet\ConnectionInterface;

class Subscriber
{
    public $uid;
    public $id;
    public $conn;
    public $subscription;

    public function __construct($uid, $id, ConnectionInterface $conn)
    {
        $this->uid = $uid;
        $this->id = $id;
        $this->conn = $conn;
    }

    public function emit($message)
    {
        $message['id'] = $this->id;
        $this->conn->send(json_encode($message));
    }

    public function subscribe(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->subscription->subscribe($this);
    }

    public function unsubscribe()
    {
        if (!is_null($this->subscription)) {
            $this->subscription->unsubscribe($this);
        }
    }
}
