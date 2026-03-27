<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Handler\SelectProductHandler;

final class SelectProductHandlerTest extends AbstractMachineHandlerTestCase
{
    public function testItPurchasesAProductAndPersistsTheUpdatedMachine(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 2, 10 => 1, 5 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        $result = $handler->handle(new SelectProductCommand('water'));
        $savedMachine = $repository->machine('default');
        $water = $result->machineSnapshot()->productSnapshotFor('water');

        self::assertNotNull($savedMachine);
        self::assertNotNull($water);
        self::assertSame('water', $result->dispensedProductSelector());
        self::assertSame('Water', $result->dispensedProductName());
        self::assertSame([], $result->dispensedChangeCounts());
        self::assertSame(0, $result->machineSnapshot()->insertedBalanceCents());
        self::assertSame(9, $water->quantity());
        self::assertSame(1, $repository->saveCount());
        self::assertSame(0, $savedMachine->insertedBalance()->cents());
    }

    public function testItTranslatesMissingMachinesIntoApplicationFailures(): void
    {
        $handler = new SelectProductHandler(
            $this->repository(),
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('water'));
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

    public function testItTranslatesUnknownSelectorsIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('chips'));
            self::fail('The handler should have rejected the unknown selector.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::ProductNotFound,
                'Unknown selector "chips".',
                [
                    'machineId' => 'default',
                    'selector' => 'chips',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesInvalidSelectorsIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('water!'));
            self::fail('The handler should have rejected the invalid selector.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::ProductNotFound,
                'Selector "water!" is invalid.',
                [
                    'machineId' => 'default',
                    'selector' => 'water!',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesOutOfStockProductsIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine(['water' => 0], [], [100 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('water'));
            self::fail('The handler should have rejected the out-of-stock product.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::ProductOutOfStock,
                'Product "Water" is out of stock.',
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesInsufficientBalanceIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine([], [], [25 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('water'));
            self::fail('The handler should have rejected the insufficient balance.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::InsufficientBalance,
                'Inserted balance "25" is insufficient for product price "65".',
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }

    public function testItTranslatesExactChangeFailuresIntoApplicationFailures(): void
    {
        $repository = $this->repository($this->machine([], [], [100 => 1]));
        $handler = new SelectProductHandler(
            $repository,
            $this->machineSnapshotFactory(),
            $this->machineFailureFactory(),
        );

        try {
            $handler->handle(new SelectProductCommand('water'));
            self::fail('The handler should have rejected the missing exact change.');
        } catch (MachineOperationFailed $exception) {
            $this->assertMachineFailure(
                $exception,
                MachineFailureCode::ExactChangeUnavailable,
                'Exact change "35" cannot be returned for selector "water".',
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            );
            self::assertSame(0, $repository->saveCount());
        }
    }
}
