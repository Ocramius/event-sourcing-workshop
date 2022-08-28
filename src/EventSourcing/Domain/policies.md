# Policies

A [`Policy`](Policy.php) is a "when this happened, make this happen" rule.

In this project, a policy is a simplified function that produces a
list of [`Command`s](../../Commanding/Domain/Command.php) to be executed
whenever a certain [`DomainEvent`](README.md) is observed.

#### Reacting to domain events

Since every change in an event-sourced system is an event, it is possible to put policies
([`Policy`](Policy.php)) in place, so that when an event is observed, further
business processes can be automated.

This allows for very complex business processes to be decomposed into many small bite-sized
state mutations, each providing continuation into the next process (which may reside in a
different subdomain).

**Important**: policies perform "follow-up work", and therefore, contrary to projections, they
**cannot** be re-run.

For example, you don't want to re-send registration mails for users that registered
years ago, so you will need to keep track of which work you've already done!

In the following example, we turn the lights on or off based on whether a door was opened or closed:

```mermaid
graph TB
    subgraph Event Store
        subgraph Light
            LightTurnedOff(LightTurnedOff)
            LightTurnedOn(LightTurnedOn)
        end
        
        subgraph Door
            DoorOpened(DoorOpened)
            DoorLocked(DoorLocked)
        end
    end
    
    subgraph Commands
        TurnOffLight
        TurnOnLight

        TurnOffLight --> LightTurnedOff
        TurnOnLight --> LightTurnedOn
    end
    
    subgraph Process Manager
        ApplyPolicies((Apply Policies))

        DoorOpened --> ApplyPolicies
        DoorLocked --> ApplyPolicies
    end
    
    subgraph Policies
        WhenDoorOpenedThenTurnOnTheLights
        WhenDoorLockedThenTurnOffTheLights
        
        ApplyPolicies --> WhenDoorOpenedThenTurnOnTheLights
        ApplyPolicies --> WhenDoorLockedThenTurnOffTheLights

        WhenDoorOpenedThenTurnOnTheLights --> TurnOnLight
        WhenDoorLockedThenTurnOffTheLights --> TurnOffLight
    end
```

## Implementation

The policy runner works as following under the hood:

```mermaid
sequenceDiagram
    loop
        Policy Runner ->> Processed Events Registry: SELECT reserve_next_event_to_process()
        Processed Events Registry ->> Policy Runner: $event_number
        Policy Runner ->> Event Stream: get($event_number)
        Event Stream ->> Policy Runner: DomainEvent
        Policy Runner ->> Policy: Compute work to be scheduled
        Policy ->> Policy Runner: list<Command>
        loop
            Policy Runner ->> Command Bus: execute command
        end
        alt success
            Policy Runner ->> Processed Events Registry: SELECT mark_event_processing_completed($event_number)
        else failure
            Policy Runner ->> Processed Events Registry: SELECT mark_event_processing_failed($event_number)
        end
    end
```