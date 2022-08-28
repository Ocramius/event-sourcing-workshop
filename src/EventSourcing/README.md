# Event Sourcing component

## What is Event Sourcing?

Event sourcing is a practice that allows representing application state through a series of "events"
that represent the full history of said state.

The idea comes from other fields of engineering, accounting, banking, legal and similar more mature fields.

For example, in the context of banking, the balance of your bank account is computed off the list of all past
transactions, and never stored as-is, as that would remove any papertrail on how the money was moved.

## Event Sourcing Concepts

1. [Domain Events: the basic building block](./Domain/README.md)
2. [Aggregate (state machines) and Aggregate Domain Events](./Domain/Aggregate/README.md)
3. [Projections: generating meaningful data structures from historical data](./Infrastructure/Projection/README.md)
4. [Policies: reacting do events](./Domain/policies.md)
