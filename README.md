# WakeOnWeb MessageBusReceiver

## Installation

`composer.json`

```
    "wakeonweb/message-bus-receiver": "^0.1"
```

If you use **Symfony**, you can load the bundle `WakeOnWeb\MessageBusReceiver\App\Bundle\WakeonwebMessageBusReceiverBundle`.

## Usage


### Amqp Input

```
wakeonweb_message_bus_receiver:
    buses:
        my_event_bus:
            bus: my_event_bus
            inputs:
                amqp:
                    message_name: EventBusExternalMessage
                    move_to_error_queue_on_error: false
            message_factory:
                mapping:
                    user_created: App\Event\UserCreatedEvent
                    foo: @id_service
```

Then in your prooph event bus definition:

```
prooph_service_bus:
  event_buses:
    my_event_bus:
        message_factory: wow.message_bus_receiver.my_event_bus.message_factory
```

Define the bus where messages will be trigerred once catched.

### Controller Route Input

```
wakeonweb_message_bus_receiver:
    buses:
        my_event_bus:
            bus: my_event_bus
            inputs:
                controller_route: ~
            message_factory:
                mapping:
                    user_created: App\Event\UserCreatedEvent
                    foo: @id_service
```

Then in your routing:

```
my_incoming_events:
    path: /incoming/events
    defaults: { _controller: 'wakeonweb.message_bus_receiver.my_event_bus.route_input:indexAction' }
    methods: [POST]
```

/!\ Supports only json body at this moment /!\

