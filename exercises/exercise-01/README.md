# Standalone recording of a temperature

Target: familiarizing with the concept of `DomainEvent`s and their persistence.

Edit and run [record-temperature.php](record-temperature.php).

1. create a new `TemperatureRecorded` [`DomainEvent`](../../src/EventSourcing/Domain/DomainEvent.php)
   in [src/TemperatureTracking/Domain](../../src/TemperatureTracking/Domain)
2. add it to the event stream (tip: use the given `$kernel`)
3. observe the final database state and discuss

Question: what was saved in the DB?
