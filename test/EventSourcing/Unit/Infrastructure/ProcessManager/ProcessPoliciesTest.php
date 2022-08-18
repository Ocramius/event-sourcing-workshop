<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Infrastructure\ProcessManager;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandBus;
use EventSourcingWorkshop\EventSourcing\Domain\Policy;
use EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Streaming\TraverseEventStream;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent1;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent2;
use EventSourcingWorkshopTest\EventSourcing\Asset\DomainEvent3;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\ProcessManager\ProcessPolicies */
final class ProcessPoliciesTest extends TestCase
{
    public function testWillProcessApplicablePolicies(): void
    {
        $event1 = DomainEvent1::dummy();
        $event2 = DomainEvent2::dummy();
        $event3 = DomainEvent3::dummy();

        $policy1Command1 = $this->createStub(Command::class);
        $policy1Command2 = $this->createStub(Command::class);
        $policy2Command1 = $this->createStub(Command::class);

        $policy1 = $this->createMock(Policy::class);
        $policy2 = $this->createMock(Policy::class);
        $policy3 = $this->createMock(Policy::class);

        $policy1->method('supportedDomainEvent')
            ->willReturn(DomainEvent1::class);
        $policy2->method('supportedDomainEvent')
            ->willReturn(DomainEvent2::class);
        $policy3->method('supportedDomainEvent')
            ->willReturn(DomainEvent3::class);

        $policy1->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($event1))
            ->willReturn([$policy1Command1, $policy1Command2]);
        $policy2->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($event2))
            ->willReturn([$policy2Command1]);
        $policy3->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo($event3))
            ->willReturn([]);

        $commandBus = $this->createMock(CommandBus::class);

        $commandBus->expects(self::exactly(3))
            ->method('__invoke')
            ->with(self::logicalOr($policy1Command1, $policy1Command2, $policy2Command1));

        $traverseStream = $this->createMock(TraverseEventStream::class);

        $traverseStream->expects(self::once())
            ->method('__invoke')
            ->with(ProcessPolicies::class)
            ->willReturn([$event1, $event2, $event3]);

        $runPolicies = new ProcessPolicies(
            [$policy1, $policy2, $policy3],
            $commandBus,
            $traverseStream
        );

        $runPolicies();
    }
}
