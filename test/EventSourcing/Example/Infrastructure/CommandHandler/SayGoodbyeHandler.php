<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Example\Infrastructure\CommandHandler;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\EventSourcing\Domain\Aggregate\AggregateRepository;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Command\SayGoodbye;
use EventSourcingWorkshopTest\EventSourcing\Example\Domain\Greeting;
use StellaMaris\Clock\ClockInterface;

/** @template-implements CommandHandler<SayGoodbye> */
final class SayGoodbyeHandler implements CommandHandler
{
    /** @psalm-param AggregateRepository<Greeting> $greetings */
    public function __construct(
        private readonly AggregateRepository $greetings,
        private readonly ClockInterface $clock,
    ) {
    }

    /** @param SayGoodbye $command */
    public function __invoke(Command $command): void
    {
        $this->greetings->save(
            $this->greetings->get($command->greeting)
                ->sayGoodbye($command->message, $this->clock->now()),
        );
    }

    public function handlesCommand(): string
    {
        return SayGoodbye::class;
    }
}
