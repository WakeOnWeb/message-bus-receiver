WakeOnWeb MessageBusReceiver
============================

Installation
------------

`composer.json`

```
    "wakeonweb/message-bus-receiver": "^0.1"
```

If you use **Symfony**, you can load the bundle `WakeOnWeb\MessageBusReceiver\App\Bundle\WakeonwebMessageBusReceiverBundle`.

Usage
-----

Example using Symfony bundle:

```
wakeonweb_message_bus_receiver:
    buses:
        async_external_incoming_event_bus:
            inputs:
                amqp:
                    message_name: EventBusExternalMessage
            message_factory:
                mapping:
                    user_created: App\Event\UserCreatedEvent
                normalizers:
                    foo: id_service
```

Define the bus where messages will be trigerred once catched.
You must define the inputs (amqp/http/...) and a mapping to normalizer messages, you can define a static KV mapping or normalizers.
