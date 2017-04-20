# GraphQL

> GraphQL is a query language for APIs and a runtime for fulfilling those queries with your existing data. GraphQL provides a complete and understandable description of the data in your API, gives clients the power to ask for exactly what they need and nothing more, makes it easier to evolve APIs over time, and enables powerful developer tools. — [graphql.org](http://graphql.org)

Where is how you can create a GraphQL endpoint using Siler's simplicity powered by the [PHP's GraphQL implemention](http://webonyx.github.io/graphql-php/).

First, let's require it:

```bash
$ composer require webonyx/graphql-php
```

Now let's define your Schema. We're going to use a chat-like domain:

<sub>schema.graphql</sub>
```graphql
type Message {
  id: Int
  roomId: Int
  body: String
  timestamp: String
}

type Room {
  id: Int
  name: String
}

type Query {
  messages(roomName: String): [Message]
  rooms: [Room]
}

type Mutation {
  start(roomName: String): Room
  chat(roomName: String, body: String): Message
}
```

Very simple, but if itsn't familiar to you take a look at [GraphQL](http://graphql.org) first since this docs will not cover what is it, but how to use it.

For each Query and Mutation we can define our resolver functions. We'll be using [RedBean](http://www.redbeanphp.com/index.php) to help us in a simple SQLite storage ORM.

<sub>resolvers.php</sub>
```php
<?php

use RedBeanPHP\R;
use Siler\Graphql;

// Setup RedBean to use a db.sqlite file at projects dir
R::setup('sqlite:'.__DIR__.'/db.sqlite');

// A helper to find a Room by its name
$roomByName = function ($name) {
    return R::findOne('room', 'name = ?', [$name]);
};

// Our messages Query resolver
$messages = function ($root, $args) use ($roomByName) {
    $roomName = $args['roomName'];
    $room = $roomByName($roomName);
    $messages = R::find('message', 'room_id = ?', [$room['id']]);

    return $messages;
};

// Our rooms Query resolver
$rooms = function () {
    return R::findAll('room');
};

// Our start Mutation resolver. It creates new rooms
$start = function ($root, $args) {
    $roomName = $args['roomName'];

    $room = R::dispense('room');
    $room['name'] = $roomName;

    R::store($room);

    return $room;
};

// Our chat Mutation resolver. It sends new messages
$chat = function ($root, $args) use ($roomByName) {
    $roomName = $args['roomName'];
    $body = $args['body'];

    $room = $roomByName($roomName);

    $message = R::dispense('message');
    $message['roomId'] = $room['id'];
    $message['body'] = $body;
    $message['timestamp'] = new \DateTime();

    R::store($message);

    return $message;
};

// Return them glued into each operation Query/Mutation
return [
    'Query' => compact('rooms', 'messages'),
    'Mutation' => compact('start', 'chat'),
];
```

Awesome. We have out type definitions and our resolve functions. Let's put them together:

<sub>schema.php</sub>
```php
<?php

use Siler\Graphql;

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');
$resolvers = include __DIR__.'/resolvers.php';

return Graphql\schema($typeDefs, $resolvers);
```

Yeah, that simple! And it's exactly where Siler does it magic happen.<br>
Thanks to webonyx/graphql-php we can parse the schema.graphql into an actual Schema and Siler will override the default field resolver to work with the given $resolvers.

Now, let's create our HTTP endpoint:

<sub>api.php</sub>
```php
<?php

use Siler\Graphql;
use Siler\Http\Request;
use Siler\Http\Response;

require 'vendor/autoload.php';

// Enable CORS
Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

// Respond only for POST requests
if (Request\method_is('post')) {
    // Retrive the Schema
    $schema = include __DIR__.'/schema.php';

    // Give it to siler
    Graphql\init($schema);
}
```

**That's it!**

Start the server:

```bash
$ php -S localhost:8000 api.php
```

Test with a Graph*i*QL app.

Here are some queries you can execute:

Query available rooms:

```graphql
query {
  rooms {
    id
    name
  }
}
```

Yeah, there isn't none yet:

```json
{
  "data": {
    "rooms": []
  }
}
```

But. **It's working!**. Thanks to RedBean + SQLite we can play around without worrying about database setup and migrations.

Creating a new Room:

```graphql
mutation newRoom($roomName: String) {
  start(roomName: $roomName) {
    id
  }
}
```

<sub>variables</sub>
```json
{
  "roomName": "graphql"
}
```

When our first room is created:

```json
{
  "data": {
    "start": {
      "id": 1
    }
  }
}
```

Call the query that fetches Rooms to check:

```graphql
query {
  rooms {
    id
    name
  }
}
```

Yup! Is there:

```json
{
  "data": {
    "rooms": [
      {
        "id": 1,
        "name": "graphql"
      }
    ]
  }
}
```

Without any Messages yet:

```graphql
query roomMessages($roomName: String) {
  messages(roomName: $roomName) {
    id
    body
    timestamp
  }
}
```

<sub>variables</sub>
```json
{
  "roomName": "graphql"
}
```

No messaes yet:

```json
{
  "data": {
    "messages": []
  }
}
```

So let's chat!

```
mutation newMessage($roomName: String) {
  chat(roomName: $roomName, body: "hello") {
    id
  }
}
```

<sub>variables</sub>
```json
{
  "roomName": "graphql"
}
```

First message created:

```json
{
  "data": {
    "chat": {
      "id": 1
    }
  }
}
```

Then let's refetch our messages Query to check:

```graphql
query roomMessages($roomName: String) {
  messages(roomName: $roomName) {
    id
    body
    timestamp
  }
}
```

<sub>variables</sub>
```json
{
  "roomName": "graphql"
}
```

Aha! Here we go:

```json
{
  "data": {
    "messages": [
      {
        "id": 1,
        "body": "hello",
        "timestamp": "2017-04-20 14:58:07"
      }
    ]
  }
}
```

Liked it? What about listening to added messages and enable real-time features?<br>
Sounds cool? That is **GraphQL Subscriptions** we are going to cover next.
