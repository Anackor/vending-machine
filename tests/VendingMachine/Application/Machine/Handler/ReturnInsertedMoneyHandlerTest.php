<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Handler\ReturnInsertedMoneyHandler;

final class ReturnInsertedMoneyHandlerTest extends AbstractMachineHandlerTestCase
{
    public function testItRefundsInsertedMoneyAndPersistsTheUpdatedMachine(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 1, 100 => 1]));
        $handler = new ReturnInsertedMoneyHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        $result = $handler->handle(new ReturnInsertedMoneyCommand());
        $savedMachine = $repository->machine('default');

        self::assertNotNull($savedMachine);
        self::assertSame([25 => 1, 100 => 1], $result->returnedCoinCounts());
        self::assertSame(0, $result->machineSnapshot()->insertedBalanceCents());
        self::assertSame([], $result->machineSnapshot()->insertedCoins());
        self::assertSame(1, $repository->saveCount());
        self::assertSame(0, $savedMachine->insertedBalance()->cents());
    }

    public function testItTranslatesMissingMachinesIntoApplicationFailures(): void
    {
        $handler = new ReturnInsertedMoneyHandler(
            $this->repository(),
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new ReturnInsertedMoneyCommand());
            self::fail('The handler should have rejected the missing machine.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::MachineNotFound,
                'Machine "default" was not found.',
                ['machineId' => 'default'],
            );
        }
    }
}
