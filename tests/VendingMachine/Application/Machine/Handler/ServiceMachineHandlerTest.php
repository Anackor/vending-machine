<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Handler\ServiceMachineHandler;

final class ServiceMachineHandlerTest extends AbstractMachineHandlerTestCase
{
    public function testItServicesTheMachineAndPersistsTheUpdatedState(): void
    {
        $repository = $this->repository($this->machine());
        $handler = new ServiceMachineHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        $result = $handler->handle(
            new ServiceMachineCommand(
                [
                    'water' => 2,
                    'juice' => 3,
                    'soda' => 4,
                ],
                [
                    5 => 1,
                    25 => 2,
                ],
            ),
        );
        $savedMachine = $repository->machine('default');
        $water = $result->machineSnapshot()->productSnapshotFor('water');

        self::assertNotNull($savedMachine);
        self::assertNotNull($water);
        self::assertSame([5 => 1, 25 => 2], $result->machineSnapshot()->availableChangeCounts());
        self::assertSame(2, $water->quantity());
        self::assertSame(1, $repository->saveCount());
        self::assertSame([5 => 1, 25 => 2], $savedMachine->availableChange()->counts());
    }

    public function testItTranslatesMissingMachinesIntoApplicationFailures(): void
    {
        $handler = new ServiceMachineHandler(
            $this->repository(),
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new ServiceMachineCommand(['water' => 1], [25 => 1]));
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

    public function testItTranslatesPendingBalancesIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 1]));
        $handler = new ServiceMachineHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(
                new ServiceMachineCommand(
                    [
                        'water' => 2,
                        'juice' => 3,
                        'soda' => 4,
                    ],
                    [25 => 1],
                ),
            );
            self::fail('The handler should have rejected the pending balance.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::PendingBalanceDuringService,
                'Machine service requires no pending customer balance.',
                ['machineId' => 'default'],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesInvalidServiceConfigurationsIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine());
        $handler = new ServiceMachineHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(
                new ServiceMachineCommand(
                    [
                        'water' => 2,
                        'soda' => 4,
                    ],
                    [25 => 1],
                ),
            );
            self::fail('The handler should have rejected the invalid service configuration.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::InvalidServiceConfiguration,
                'Missing stock count for selector "juice".',
                ['machineId' => 'default'],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }
}
