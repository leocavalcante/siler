# GraphQL Subscriptions

Here is how you can add real-time capabilities to your GraphQL applications.

**Siler** implementation is based on [Apollo's WebSocket transport layer](https://github.com/apollographql/subscriptions-transport-ws).

<iframe width="560" height="315" src="https://www.youtube.com/embed/wo9XFmW0W2c" frameborder="0" allowfullscreen></iframe>

We'll need some help to get WebSockets working, so let's require two libraries.

One is for the server-side:

```bash
$ composer require cboden/ratchet
```

And the other is for the client-side:

```bash
$ composer require ratchet/pawl
```

We are going to extend our [previous guide](README.md), so if you didn't read it, [take a look](README.md) before continue.

First, let's add our **Subscription** type to our Schema:

###### schema.graphql

```graphql
# (...previous work...)

type Subscription {
  inbox(roomName: String): Message
}
```

Simple like that. We can subscribe to inboxes to receive new messages.

And our resolver will look like that:

###### resolvers.php

```php
# (...previous work...)

$subscriptionType = [
    'inbox' => function ($message) {
        return $message;
    },
];
```

Yeap, it is just resolving to the message that it receives.

** But where this message comes from?**

## Siler powers!

Siler has a function at the `Graphql` namespace to define where your subscriptions are running:

```php
Graphql\subscriptions_at('ws://127.0.0.1:8080');
```

Here we are assuming that they are running at localhost on port 8080.<br>

### And why is that for?

This is just a helper that adds the **Subscriptions endpoint** to the Siler container so another function can actually use it when needed. And this function is `publish`!

`Siler\Graphql\publish` will make a WebSocket call to the Subscriptions server notifying that something has happened.

```php
Graphql\publish('inbox', $message);
```

It's first argument is the **Subscription** that will be triggered and the second argument is a data payload, in our case, the new message that has been created.

Our `resolvers.php` will look like this:

###### resolvers.php

```php
<?php

use RedBeanPHP\R;
use Siler\Graphql;

R::setup('sqlite:'.__DIR__.'/db.sqlite');

// Here we set where our subscriptions are running
Graphql\subscriptions_at('ws://127.0.0.1:8080');

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
        Graphql\publish('inbox', $message); // <- Exactly what "inbox" will receive

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

## Starting the server

As in `api.php` endpoint we need to setup the Subscriptions server:

###### subscriptions.php

```php
<?php

use Siler\Graphql;

require 'vendor/autoload.php';

$schema = include __DIR__.'/schema.php';
Graphql\subscriptions($schema)->run();
```

Yeah, easy like that. Let Siler do the boring stuff. You just give a Schema to the `subscriptions` function. This function will return an `IoServer` where you can call the `run` method.

```
php subscriptions.php
```

By default, Siler will run the subscriptions at localhost on port 8080.

## That's it

No kidding.

Behind the scenes [ReactPHP](http://reactphp.org/), [Ratchet](http://socketo.me/) and [Pawl](https://github.com/ratchetphp/Pawl) are doing the hard work of handling WebSocket communication protocol while **Siler** is doing the work of adding resolver resolution to [webonyx/graphql-php](https://github.com/webonyx/graphql-php) and handling [Apollo's sub-protocol](https://github.com/apollographql/subscriptions-transport-ws#client-server-communication).

### Ready to test?

You'll need a [Graph*i*QL](https://github.com/graphql/graphiql) app with Subscriptions enabled. For example: [leocavalcante/graphiql-app](https://github.com/leocavalcante/graphiql-app).

A subscription query looks like this:

```graphql
subscription newMessages($roomName: String) {
  inbox(roomName: $roomName) {
    id
    body
  }
}
```

###### variables

```json
{
  "roomName": "graphql"
}
```

The result will not be immediate since we are now listening to new messages, not querying them.

Let's take a mutation from the [previous guide](README.md). Open the Graph*i*QL app in another tab and execute:

```graphql
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

Now go back to the subscription tab and see the update!

```json
{
  "inbox": {
    "id": 42,
    "body": "hello"
  }
}
```

**Awesome!** Just one thing you probably have noticed. We have subscribed to all rooms. Make a test: create another Room if you haven't already and add a new message to that to see that our subscription tab, with `{"roomName": "graphql"}` as variables, just got this new message. How to solve this?

## Filters

Filters are part of **Siler** and based on Apollo's setup functions. Filter functions receives the published payload data as the first argument and the subscription variables as the second, so you can use them to perform matches:

###### subscriptions.php

```php
<?php

use Siler\Graphql;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

$filters = [
    'inbox' => function ($payload, $vars) {
        return $payload['room_name'] == $vars['roomName'];
    },
];

$schema = include __DIR__.'/schema.php';
Graphql\subscriptions($schema, $filters)->run();
```

As you can see, we have extend our Subscriptions endpoint adding filters. The filters array keys should match corresponding Subscription names, in our case: `inbox`. We are just checking if the given payload `room_name` is the same as the provided by the Subscription variable `roomName`. **Siler** will perform this checks for each subscription before trying to resolve and broadcast them.

We need just a little thing to get working. Adding this `room_name` field to our payload since Message only has the `room_id`. At the chat resolver, before the publish, add this line:

```php
$message['roomName'] = $roomName; // For the inbox filter
Graphql\publish('inbox', $message); // <- Exactly what "inbox" will receive
```

*Note: when a RedBeanObject is encoded to JSON it automatically converts camel case properties to underscore ones. That is why we give `roomName`, but receives as `room_name`.*

And that should be enough to solve our problem, now you only receive data from the subscribed rooms. **Enjoy!**
