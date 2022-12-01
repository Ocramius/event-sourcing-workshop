# Domain Events

State mutations in an event-source system are described through **domain events**.

A domain event is an **immutable** payload with following properties:

1. it is **pertinent** to the current business domain
2. it has a **name**, describing what it is about
    * this should be to the past tense, and the reader should understand what is going on from its name
    * i.e. `CustomerCompletedPayment` or `SubscriptionRenewed`
3. it contains **point in time** at which it occurred
4. it contains further **contextual information** necessary to understand the event in isolation

A good domain event attempts to answer to the ["Five W" questions](https://en.wikipedia.org/wiki/Five_Ws):

* **Who** - context
* **What** - name of the event
* **When** - time at which the event was raised
* **Where** - context
* **Why** - context (although not always necessary: sometimes we don't know why something happened)

In the scope of this application, a [`DomainEvent`](./DomainEvent.php) interface was defined.

---

## Producing events

Events are generally (but not exclusively) raised in two ways:

1. direct recording of an occurred fact (standalone domain event)
    * for example:
        * `TemperatureRecorded`
        * `EmailReceived`
        * `ADayHasPassed`
2. as part of a state mutation in a process under our own control (an [`Aggregate`](./Aggregate/Aggregate.php))
    * for example
        * in a `ShoppingCart` aggregate:
            * `ItemAddedToShoppingCart`
            * `ItemRemovedFromShoppingCart`
            * `ShoppingCartPurchased`
        * in a `Shipment` aggregate:
            * `ShipmentAssembled`
            * `ShipmentShipped`
            * `ShipmentDelivered`
            * `ShipmentLost`

An event must first be persisted to the event store, before being passed on to further systems.

---

### Saving **standalone** domain events

Storing a standalone domain event akin an `INSERT` operation:

```sql
INSERT INTO event_stream (event_type, time_of_recording, payload)
VALUES (:nameOfTheEvent, :timeOfEventCreation, :eventContext);
```

In this project, you can use the [`EventStore`](./Infrastructure/Persistence/EventStore.php) abstraction
to save events:

```php
/** @var $clock \Psr\Clock\ClockInterface */
/** @var $eventStore \EventSourcingWorkshop\EventSourcing\Infrastructure\Persistence\EventStore */
$eventStore->save(
    new TemperatureRecorded('roof', $sensors->roof->temperature(), $clock->now()),
    new TemperatureRecorded('floor', $sensors->floor->temperature(), $clock->now()),
    new TemperatureRecorded('basement', $sensors->basement->temperature(), $clock->now()),
);
```

**Note:** the event store only supports `INSERT` operations: `UPDATE` and `DELETE` are not supported. This
is by design, since history doesn't change, after it was recorded.

**Note:** in this workshop, we store the events in a relational database, but you can pick any storage
technology that guarantees durable, atomic persistence of events.

**Note:**: This is like blockchain, minus the bullshit.

## Process domain events

Processes and their domain events are covered under ["aggregates"](Aggregate/README.md).