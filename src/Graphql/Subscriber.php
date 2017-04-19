<?php

namespace Siler\Graphql;

use Ratchet\ConnectionInterface;

class Subscriber
{
    public $id;
    public $conn;

    public function __construct($id, ConnectionInterface $conn)
    {
        $this->id = $id;
        $this->conn = $conn;
    }

    public function emit($message)
    {
        $message['id'] = $this->id;
        $this->conn->send(json_encode($message));
    }
}
