# GraphQL

> GraphQL is a query language for APIs and a runtime for fulfilling those queries with your existing data. GraphQL provides a complete and understandable description of the data in your API, gives clients the power to ask for exactly what they need and nothing more, makes it easier to evolve APIs over time, and enables powerful developer tools. — [graphql.org](http://graphql.org)

Here is how you can create a GraphQL endpoint using Siler's simplicity powered by the [PHP's GraphQL implemention](http://webonyx.github.io/graphql-php/).

First, let's require it:

```bash
$ composer require webonyx/graphql-php
```

Now let's define our Schema. We're going to use a chat-like domain:

###### schema.graphql

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
  messages: [Message]
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

Very simple, but if it's not familiar to you, take a look at [GraphQL](http://graphql.org) first since this docs will not cover what is it, but how to use it.

For each Query and Mutation we can define our resolver functions. We'll be using [RedBean](http://www.redbeanphp.com/index.php) to help us as a simple SQLite storage ORM.

###### resolvers.php

```php
<?php

use RedBeanPHP\R;
use Siler\Graphql;

R::setup('sqlite:'.__DIR__.'/db.sqlite');

$roomByName = function ($name) {
    return R::findOne('room', 'name = ?', [$name]);
};

$roomType = [
    'messages' => function ($room) {
        return R::findAll('message', 'room_id = ?', [$room['id']]);
    },
];

$queryType = [
    'rooms' => function () {
        return R::findAll('room');
    },
    'messages' => function () use ($roomByName) {
        $roomName = $args['roomName'];
        $room = $roomByName($roomName);
        $messages = R::find('message', 'room_id = ?', [$room['id']]);

        return $messages;
    },
];

$mutationType = [
    'start' => function ($root, $args) {
        $roomName = $args['roomName'];

        $room = R::dispense('room');
        $room['name'] = $roomName;

        R::store($room);

        return $room;
    },
    'chat' => function ($root, $args) use ($roomByName) {
        $roomName = $args['roomName'];
        $body = $args['body'];

        $room = $roomByName($roomName);

        $message = R::dispense('message');
        $message['roomId'] = $room['id'];
        $message['body'] = $body;
        $message['timestamp'] = new \DateTime();

        R::store($message);

        return $message;
    },
];

return [
    'Room'     => $roomType,
    'Query'    => $queryType,
    'Mutation' => $mutationType,
];
```

Awesome. We have type definitions and resolver functions. Let's put them together in a Schema:

###### schema.php

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

###### api.php

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

### **That's it!**

Start the server:

```bash
$ php -S localhost:8000 api.php
```

Test with a [Graph*i*QL](https://github.com/graphql/graphiql) app.

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

Yeah, there isn't any Rooms yet:

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

###### variables

```json
{
  "roomName": "graphql"
}
```

Then our first room is created:

```json
{
  "data": {
    "start": {
      "id": 1
    }
  }
}
```

Call the query that fetches Rooms to check again:

```graphql
query {
  rooms {
    id
    name
  }
}
```

Yup! It's there:

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

###### variables

```json
{
  "roomName": "graphql"
}
```

No messages yet:

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

###### variables

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

Let's refetch our messages to check:

```graphql
query roomMessages($roomName: String) {
  messages(roomName: $roomName) {
    id
    body
    timestamp
  }
}
```

###### variables

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
