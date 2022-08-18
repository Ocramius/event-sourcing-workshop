<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\CommandHandler;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command\SayHello;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Greeting;
use StellaMaris\Clock\ClockInterface;

/** @template-implements CommandHandler<SayHello> */
final class SayHelloHandler implements CommandHandler
{
    /** @psalm-param AggregateRepository<Greeting> $greetings */
    public function __construct(
        private readonly AggregateRepository $greetings,
        private readonly ClockInterface $clock
    ) {
    }

    /** @param SayHello $command */
    public function __invoke(Command $command): void
    {
        $this->greetings->save(Greeting::sayHello($command->message, $this->clock->now()));
    }

    public function handlesCommand(): string
    {
        return SayHello::class;
    }
}
