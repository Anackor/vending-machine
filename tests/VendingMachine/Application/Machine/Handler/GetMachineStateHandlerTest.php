<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Handler\GetMachineStateHandler;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;

final class GetMachineStateHandlerTest extends AbstractMachineHandlerTestCase
{
    public function testItReturnsTheCurrentMachineSnapshotWithoutPersisting(): void
    {
        $repository = $this->repository($this->machine([], [25 => 2], [10 => 1]));
        $handler = new GetMachineStateHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        $result = $handler->handle(new GetMachineStateQuery());

        self::assertSame(10, $result->machineSnapshot()->insertedBalanceCents());
        self::assertSame([10 => 1], $result->machineSnapshot()->insertedCoins());
        self::assertSame([25 => 2], $result->machineSnapshot()->availableChangeCounts());
        self::assertCount(3, $result->machineSnapshot()->products());
        self::assertSame(0, $repository->saveCount());
    }

    public function testItTranslatesMissingMachinesIntoApplicationFailures(): void
    {
        $handler = new GetMachineStateHandler(
            $this->repository(),
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new GetMachineStateQuery());
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
