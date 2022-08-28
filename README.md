# EventSourcing Workshop

In here, you will find a "from scratch" PHP-based implementation of Event-Sourcing, kept to a minimum
on purpose, to allow workshop attendees to explore and experiment with its concepts.

### DISCLAIMER: Not a production project

This is **not** a library nor production-ready project: this is an educational project.
Its target audience is students and backend engineers that want to get better at abstracting/maintaining business logic.

## Knowledge required

To work in this project, you need some rough knowledge about:

* `php`
    * you should be familiar with `php`'s syntax
    * the provided test suite and tooling should help you prevent mistakes, but you should have basic knowledge
      of how `php` runs and fails.
    * we use functional and object-oriented paradigms: you may be up for a crash-course, if you never saw code
      structured this way.
* `composer`
    * `php` class autoloading and dependency installation is handled for you, but if something goes wrong, you
      should know some `composer` basics
* `sql`
    * the entire workshop is based on SQLite databases: it's just files, but you need to know some very basic SQL
* `make`
    * most repetitive tasks have been added to a [`Makefile`](./Makefile): run `make` to see what's available
* `docker` and `docker-compose`
    * to ensure everyone runs on the same environment, we run tests inside a docker container.
      Building images and starting/stopping containers is sufficient: we will not use any advanced docker features
    * don't worry too much if you don't: the [`Makefile`](./Makefile) should abstract most docker stuff away

## Firing it up

Run:

```sh
make composer-install
make quality-assurance
make interactive-shell
```

## Architecture

* [`EventSourcingWorkshop\EventSourcing` documentation](./src/EventSourcing/README.md)
* [`EventSourcingWorkshop\Commanding` documentation](./src/Commanding/README.md)
* [example implementation](./test/EventSourcing/Example/README.md) used in integration tests

---

## Exercises

Note: all exercises are under the watchful eye of static analysis and CS tooling.
Use `make quality-assurance` to validate your current work!

### 1 - Standalone recording of a temperature

Edit and run [bin/exercise-01-record-temperature.php](bin/exercise-01-record-temperature.php).

1. create a new `TemperatureRecorded` [`DomainEvent`](src/EventSourcing/Domain/DomainEvent.php)
   in [src/TemperatureTracking/Domain](src/TemperatureTracking/Domain)
2. add it to the event stream (tip: use the given `$kernel`)
3. observe the final database state and discuss

Question: what was saved in the DB?

### 2 - Tracking the last recorded temperature at each location

Edit and run [bin/exercise-02-project-last-temperature.php](bin/exercise-02-project-last-temperature.php).

1. create an iterator over the event stream (tip: use the given `$kernel`)
2. generate a `map<string, float>` containing the last known temperature at each location
3. save the generated map

Question: what happens when you run the script multiple times?
Question: can you record new temperatures and make them affect your state?

### 3 - Tracking the average recorded temperature at each location, but in the database

Edit and run [bin/exercise-02-project-last-temperature.php](bin/exercise-03-project-average-temperature.php).

1. write a DB migration with your own table (see [existing migrations](src/EventSourcing/Infrastructure/Migration))
2. write a [`DbTableProjectionDefinition`](src/EventSourcing/Infrastructure/Projection/DbTableProjectionDefinition.php)
   that defines what we should do for each event.
   See [an example projection definition](test/EventSourcing/Example/Infrastructure/Projection/PendingGoodbyes.php) for
   inspiration.
3. create a [`ProcessProjectionOnTable`](src/EventSourcing/Infrastructure/Projection/ProcessProjectionOnTable.php)
   and run it
4. run your script, observe the database state

### 4 - Send an alert when the temperature is below freezing point

Edit and run [bin/exercise-04-alert-if-temperature-below-zero.php](bin/exercise-04-alert-if-temperature-below-zero.php).

1. create a new `WhenTemperatureBelowZeroSendAlert` [`Policy`](src/EventSourcing/Domain/Policy.php)
2. create a new `SendTemperateBelowZeroAlert` [`Command`](src/Commanding/Domain/Command.php)
3. create a new `HandleSendTemperateBelowZeroAlert` [`CommandHandler`](src/Commanding/Infrastructure/CommandHandler.php)
   It should only print some alert message to `STDERR` via `error_log()`, for now.
4. wire it together with [`ProcessPolicies`](src/EventSourcing/Infrastructure/ProcessManager/ProcessPolicies.php)
5. run it, see if you can get the alerts fired

Question: what happens when you run the script multiple times?
Question: what happens when new events appear, and you run the script again?
Question: how should we deal with failures/crashes here?

### 5 - Payment aggregate

This exercise is a bit more complex, and shows how to work with event-sourced
[`Aggregate`](src/EventSourcing/Domain/Aggregate/Aggregate.php) objects.

The idea is as follows: we have a [`Payment`](src/Payment/Domain/Aggregate/Payment.php) that we can initiate with
a given [`Amount`](src/Payment/Domain/Amount.php) and [`DebtorEmail`](src/Payment/Domain/DebtorEmail.php).

The payment can be marked as completed, but it also has a deadline: whenever the deadline is passed, we want to
notify the associated `DebtorEmail`.

The final aim is to have a message printed out by our background processes whenever a debtor is notified of a
late payment.

To do that, we need to:

1. design a [`projection`](src/EventSourcing/Infrastructure/Projection/DbTableProjectionDefinition.php) that keeps
   track of currently active payments, as well as their deadlines.
   Edit [`bin/exercise-05-project-payment-deadlines.php`](bin/exercise-05-project-payment-deadlines.php).
2. have a way to start a payment flow.
   Edit [`bin/exercise-05-request-payment.php`](bin/exercise-05-request-payment.php).
3. have a way to complete a payment flow.
   Edit [`bin/exercise-05-record-payment-received.php`](bin/exercise-05-record-payment-received.php).
4. inject an [`ADayHasPassed`](src/TimeTracking/Domain/DomainEvent/ADayHasPassed.php) event in our system.
   Run [`bin/exercise-05-record-day-passed.php`](bin/exercise-05-record-day-passed.php) (this part
   is already functional / no need to edit).
5. react to [`ADayHasPassed`](src/TimeTracking/Domain/DomainEvent/ADayHasPassed.php) events with a policy.
   Edit [`bin/exercise-05-run-payment-process.php`](bin/exercise-05-run-payment-process.php)

### 6 - Collaborative event-storming

* [ ] TODO: idea of mapping a speed-trap fine management process
* [ ] TODO: idea of mapping a hotel reservation + stay process

## License

This software is proprietary: please contact the author for permission to use, but for now, these sources are not
freely reusable outside educational purposes. Yes: you are reading correctly, this is not MIT/BSD software :-P 