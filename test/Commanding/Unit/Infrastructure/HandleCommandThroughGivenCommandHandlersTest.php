<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\Commanding\Unit\Infrastructure;

use EventSourcingWorkshop\Commanding\Domain\Command;
use EventSourcingWorkshop\Commanding\Infrastructure\CommandHandler;
use EventSourcingWorkshop\Commanding\Infrastructure\Exception\CommandNotHandled;
use EventSourcingWorkshop\Commanding\Infrastructure\HandleCommandThroughGivenCommandHandlers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\Commanding\Infrastructure\HandleCommandThroughGivenCommandHandlers */
final class HandleCommandThroughGivenCommandHandlersTest extends TestCase
{
    public function testWillForwardCommandHandlingToCorrectCommandHandler(): void
    {
        /** @var CommandHandler<Command>&MockObject $handler1 */
        $handler1 = $this->createMock(CommandHandler::class);
        /** @var CommandHandler<Command>&MockObject $handler2 */
        $handler2 = $this->createMock(CommandHandler::class);

        $command1 = $this->getMockBuilder(Command::class)
            ->setMockClassName('Command1')
            ->getMock();
        $command2 = $this->getMockBuilder(Command::class)
            ->setMockClassName('Command2')
            ->getMock();

        $handler1->method('handlesCommand')
            ->willReturn(get_class($command1));
        $handler2->method('handlesCommand')
            ->willReturn(get_class($command2));

        $handler1->expects(self::once())
            ->method('__invoke')
            ->with($command1);
        $handler2->expects(self::once())
            ->method('__invoke')
            ->with($command2);

        $commandBus = new HandleCommandThroughGivenCommandHandlers([$handler1, $handler2]);

        $commandBus($command1);
        $commandBus($command2);
    }

    public function testWillRaiseAnExceptionWhenCommandsCouldNotBeHandled(): void
    {
        /** @var CommandHandler<Command>&MockObject $handler */
        $handler = $this->createMock(CommandHandler::class);

        $command1 = $this->getMockBuilder(Command::class)
            ->setMockClassName('Command1')
            ->getMock();
        $command2 = $this->getMockBuilder(Command::class)
            ->setMockClassName('Command2')
            ->getMock();

        $handler->method('handlesCommand')
            ->willReturn(get_class($command1));

        $handler->expects(self::never())
            ->method('__invoke');

        $commandBus = new HandleCommandThroughGivenCommandHandlers([$handler]);

        $this->expectException(CommandNotHandled::class);

        $commandBus($command2);
    }
}
