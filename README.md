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

1. [Standalone recording of a temperature](exercises/exercise-01/README.md)
2. [Tracking the last recorded temperature at each location](exercises/exercise-02/README.md)
3. [Tracking the average recorded temperature at each location, but in the database](exercises/exercise-03/README.md)
4. [Send an alert when the temperature is below freezing point](exercises/exercise-04/README.md)
5. [Payment aggregate](exercises/exercise-05/README.md)
6. Collaborative event-sourcing:
    * [ ] TODO: idea of mapping a speed-trap fine management process
    * [ ] TODO: idea of mapping a hotel reservation + stay process

## License

This software is proprietary: please contact the author for permission to use, but for now, these sources are not
freely reusable outside educational purposes. Yes: you are reading correctly, this is not MIT/BSD software :-P 