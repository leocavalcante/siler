# GraphQL

> GraphQL is a query language for APIs and a runtime for fulfilling those queries with your existing data. GraphQL provides a complete and understandable description of the data in your API, gives clients the power to ask for exactly what they need and nothing more, makes it easier to evolve APIs over time, and enables powerful developer tools. — [graphql.org](http://graphql.org/)

Here is how you can create a GraphQL endpoint using Siler's simplicity powered by the [PHP's GraphQL implemention](http://webonyx.github.io/graphql-php/).

First, let's require it:

```text
$ composer require webonyx/graphql-php
```

{% hint style="info" %}
Siler doesn't have direct dependencies, to stay fit, it favors peer dependencies, which means you have to explicitly declare a `graphql` dependency in your project in order to use it.
{% endhint %}

Now let's define our Schema. We're going to use a chat-like domain:

{% code title="schema.graphql" %}
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
{% endcode %}

Very simple, but if it's not familiar to you, take a look at [GraphQL](http://graphql.org/) first since this docs will not cover what is it, but how to use it.

For each Query and Mutation we can define our resolver functions. We'll be using [RedBean](http://www.redbeanphp.com/index.php) to help us as a simple SQLite storage ORM.

{% code title="resolvers.php" %}
```php
<?php

use RedBeanPHP\R;

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
    'messages' => function ($root, $args) use ($roomByName) {
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
{% endcode %}

Awesome. We have type definitions and resolver functions. Let's put them together in a Schema:

{% code title="schema.php" %}
```php
<?php

use Siler\GraphQL;

$typeDefs = file_get_contents(__DIR__.'/schema.graphql');
$resolvers = include __DIR__.'/resolvers.php';

return GraphQL\schema($typeDefs, $resolvers);
```
{% endcode %}

Yeah, that simple! And it's exactly where Siler does it magic happen.  
Thanks to `webonyx/graphql-php` we can parse the `schema.graphql` into an actual Schema and Siler will override the default field resolver to work with the given `$resolvers`.

Now, let's create our HTTP endpoint:

{% code title="api.php" %}
```php
<?php

use Siler\GraphQL;
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
    GraphQL\init($schema);
}
```
{% endcode %}

#### **That's it!**

Start the server:

```bash
$ php -S localhost:8000 api.php
```

\*\*\*\*[**You can use Prima's GraphQL Playground to test it.**](https://github.com/prisma/graphql-playground)\*\*\*\*

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

```javascript
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

**variables**

```javascript
{
  "roomName": "graphql"
}
```

Then our first room is created:

```javascript
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

```javascript
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

**variables**

```javascript
{
  "roomName": "graphql"
}
```

No messages yet:

```javascript
{
  "data": {
    "messages": []
  }
}
```

So let's chat!

```graphql
mutation newMessage($roomName: String) {
  chat(roomName: $roomName, body: "hello") {
    id
  }
}
```

**variables**

```javascript
{
  "roomName": "graphql"
}
```

First message created:

```javascript
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

**variables**

```javascript
{
  "roomName": "graphql"
}
```

Aha! Here we go:

```javascript
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

Liked it? What about listening to added messages and enable real-time features?  
Sounds cool? That is **GraphQL Subscriptions** and we are going to cover next.

### GraphQL Subscriptions

Here is how you can add real-time capabilities to your GraphQL applications.

**Siler** implementation is based on [Apollo's WebSocket transport layer](https://github.com/apollographql/subscriptions-transport-ws).

{% embed url="https://www.youtube.com/embed/wo9XFmW0W2c" %}

We'll need some help to get WebSockets working, so let's require two libraries.

One is for the server-side:

```text
$ composer require cboden/ratchet
```

And the other is for the client-side:

```text
$ composer require ratchet/pawl
```

First, let's add our **Subscription** type to our Schema:

{% code title="schema.graphql" %}
```graphql
# (...previous work...)

type Subscription {
  inbox(roomName: String): Message
}
```
{% endcode %}

Simple like that. We can subscribe to inboxes to receive new messages.

And our resolver will look like that:

{% code title="resolvers.php" %}
```php
# (...previous work...)

$subscriptionType = [
    'inbox' => function ($message) {
        return $message;
    },
];
```
{% endcode %}

Yeap, it is just resolving to the message that it receives.

**But where this message comes from?**

#### Siler powers!

Siler has a function at the `Graphql` namespace to define where your subscriptions are running:

```php
GraphQL\subscriptions_at('ws://127.0.0.1:3000');
```

Here we are assuming that they are running at localhost on port 8080.  


#### And why is that for?

This is just a helper that adds the **Subscriptions endpoint** to the Siler container so another function can actually use it when needed. And this function is `publish`!

`Siler\GraphQL\publish` will make a WebSocket call to the Subscriptions server notifying that something has happened.

```php
GraphQL\publish('inbox', $message);
```

It's first argument is the **Subscription** that will be triggered and the second argument is a data payload, in our case, the new message that has been created.

Our `resolvers.php` will look like this:

{% code title="resolvers.php" %}
```php
<?php

use RedBeanPHP\R;
use Siler\GraphQL;

R::setup('sqlite:'.__DIR__.'/db.sqlite');

// Here we set where our subscriptions are running
GraphQL\subscriptions_at('ws://127.0.0.1:3000');

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

        // Then we can publish new messages that arrives from the chat mutation
        GraphQL\publish('inbox', $message); // <- Exactly what "inbox" will receive

        return $message;
    },
];

// Our added Subscription type
$subscriptionType = [
    'inbox' => function ($message) { // <- Received from "publish"
        return $message;
    },
];

return [
    'Room'         => $roomType,
    'Query'        => $queryType,
    'Mutation'     => $mutationType,
    'Subscription' => $subscriptionType, // Add to the resolver functions array
];
```
{% endcode %}

### Starting the server

As in `api.php` endpoint we need to setup the Subscriptions server:

{% code title="subscriptions.php" %}
```php
<?php

use Siler\GraphQL;

require 'vendor/autoload.php';

$schema = include __DIR__.'/schema.php';
GraphQL\subscriptions($schema, [], '0.0.0.0', 3000)->run();
```
{% endcode %}

Yeah, easy like that. Let Siler do the boring stuff. You just give a Schema to the `subscriptions` function. This function will return an `IoServer` where you can call the `run` method.

```bash
php subscriptions.php
```

By default, Siler will run the subscriptions at localhost on port 8080.

### Production-grade

To run subscriptions server at a production-grade level, please consider using some long-running process manager like Supervisor. Take a look at [http://socketo.me/docs/deploy\#supervisor](http://socketo.me/docs/deploy#supervisor).

#### That's it

No kidding.

Behind the scenes [ReactPHP](http://reactphp.org/), [Ratchet](http://socketo.me/) and [Pawl](https://github.com/ratchetphp/Pawl) are doing the hard work of handling WebSocket communication protocol while **Siler** is doing the work of adding resolver resolution to [webonyx/graphql-php](https://github.com/webonyx/graphql-php) and handling [Apollo's sub-protocol](https://github.com/apollographql/subscriptions-transport-ws#client-server-communication).

#### Ready to test?

\*\*\*\*[**You can use Prisma's GraphQL Playground to test the subscriptions as well.**](https://github.com/prisma/graphql-playground)\*\*\*\*

A subscription query looks like this:

```graphql
subscription newMessages($roomName: String) {
  inbox(roomName: $roomName) {
    id
    body
  }
}
```

**variables**

```javascript
{
  "roomName": "graphql"
}
```

The result will not be immediate since we are now listening to new messages, not querying them.

Let's take a mutation from the [previous guide](https://github.com/leocavalcante/siler/blob/master/docs/graphql/README.md). Open the Graph_i_QL app in another tab and execute:

```graphql
mutation newMessage($roomName: String) {
  chat(roomName: $roomName, body: "hello") {
    id
  }
}
```

**variables**

```javascript
{
  "roomName": "graphql"
}
```

Now go back to the subscription tab and see the update!

```graphql
{
  "inbox": {
    "id": 42,
    "body": "hello"
  }
}
```

**Awesome!** Just one thing you probably have noticed. We have subscribed to all rooms. Make a test: create another Room if you haven't already and add a new message to that to see that our subscription tab, with `{"roomName": "graphql"}` as variables, just got this new message. How to solve this?

### Filters

Filters are part of **Siler** and based on Apollo's setup functions. Filter functions receives the published payload data as the first argument and the subscription variables as the second, so you can use them to perform matches:

{% code title="subscriptions.php" %}
```php
<?php

use Siler\GraphQL;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$schema = include __DIR__.'/schema.php';
GraphQL\subscriptions($schema, $filters)->run();
```
{% endcode %}

As you can see, we have extend our Subscriptions endpoint adding filters. The filters array keys should match corresponding Subscription names, in our case: `inbox`. We are just checking if the given payload `room_name` is the same as the provided by the Subscription variable `roomName`. **Siler** will perform this checks for each subscription before trying to resolve and broadcast them.

We need just a little thing to get working. Adding this `room_name` field to our payload since Message only has the `room_id`. At the chat resolver, before the publish, add this line:

```php
$message['roomName'] = $roomName; // For the inbox filter
GraphQL\publish('inbox', $message); // <- Exactly what "inbox" will receive
```

{% hint style="info" %}
When a `RedBeanObject` is encoded to JSON it automatically converts camel case properties to underscore ones. That is why we give `roomName`, but receive as `room_name`.
{% endhint %}

And that should be enough to solve our problem, now you only receive data from the subscribed rooms. **Enjoy!**

