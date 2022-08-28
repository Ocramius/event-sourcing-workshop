# Payment aggregate

Target: understanding event-sourced aggregates, and how they abstract state machines.

This exercise is a bit more complex, and shows how to work with event-sourced
[`Aggregate`](../../src/EventSourcing/Domain/Aggregate/Aggregate.php) objects.

The idea is as follows: we have a [`Payment`](../../src/Payment/Domain/Aggregate/Payment.php) that we can initiate with
a given [`Amount`](../../src/Payment/Domain/Amount.php) and [`DebtorEmail`](../../src/Payment/Domain/DebtorEmail.php).

The payment can be marked as completed, but it also has a deadline: whenever the deadline is passed, we want to
notify the associated `DebtorEmail`.

The final aim is to have a message printed out by our background processes whenever a debtor is notified of a
late payment.

To do that, we need to:

1. design a [`projection`](../../src/EventSourcing/Infrastructure/Projection/DbTableProjectionDefinition.php) that keeps
   track of currently active payments, as well as their deadlines.
   Edit [`01-project-payment-deadlines.php`](01-project-payment-deadlines.php).
2. have a way to start a payment flow.
   Edit [`02-request-payment.php`](02-request-payment.php).
3. have a way to complete a payment flow.
   Edit [`03-record-payment-received.php`](03-record-payment-received.php).
4. inject an [`ADayHasPassed`](../../src/TimeTracking/Domain/DomainEvent/ADayHasPassed.php) event in our system.
   Run [`04-record-day-passed.php`](04-record-day-passed.php) (this part
   is already functional / no need to edit).
5. react to [`ADayHasPassed`](../../src/TimeTracking/Domain/DomainEvent/ADayHasPassed.php) events with a policy.
   Edit [`05-run-payment-process.php`](05-run-payment-process.php)