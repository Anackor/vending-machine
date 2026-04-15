<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Handler\InsertCoinHandler;

final class InsertCoinHandlerTest extends AbstractMachineHandlerTestCase
{
    public function testItInsertsACoinAndPersistsTheUpdatedMachine(): void
    {
        $repository = $this->repository($this->machine());
        $handler = new InsertCoinHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        $result = $handler->handle(new InsertCoinCommand(25));
        $savedMachine = $repository->machine('default');

        self::assertNotNull($savedMachine);
        self::assertSame(25, $result->machineSnapshot()->insertedBalanceCents());
        self::assertSame([25 => 1], $result->machineSnapshot()->insertedCoins());
        self::assertSame(1, $repository->saveCount());
        self::assertSame('default', $repository->lastSavedMachineId());
        self::assertSame(25, $savedMachine->insertedBalance()->cents());
    }

    public function testItTranslatesUnsupportedCoinsIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine());
        $handler = new InsertCoinHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new InsertCoinCommand(50));
            self::fail('The handler should have rejected the unsupported coin.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::UnsupportedCoin,
                'Unsupported coin denomination.',
                [
                    'coinCents' => 50,
                    'machineId' => 'default',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesMissingMachinesIntoApplicationFailures(): void
    {
        $handler = new InsertCoinHandler(
            $this->repository(),
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new InsertCoinCommand(25));
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
