<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Handler;

use PHPUnit\Framework\TestCase;
use Tests\VendingMachine\Application\Machine\Double\InMemoryMachineRepository;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\InsertedCoins;
use VendingMachine\Domain\Machine\ValueObject\Money;
use VendingMachine\Domain\Machine\ValueObject\Selector;

abstract class AbstractMachineHandlerTestCase extends TestCase
{
    /**
     * @param array<string, bool|float|int|string> $context
     */
    protected function assertMachineFailure(
        MachineOperationFailed $exception,
        MachineFailureCode $code,
        string $message,
        array $context,
    ): void {
        self::assertSame($code, $exception->failure()->code());
        self::assertSame($message, $exception->failure()->message());
        self::assertSame($context, $exception->failure()->context());
        self::assertSame($message, $exception->getMessage());
    }

    /**
     * @param array<string, int> $stockQuantities
     * @param array<int|string, mixed> $availableChangeCounts
     * @param array<int|string, mixed> $insertedCoinCounts
     */
    protected function machine(
        array $stockQuantities = [],
        array $availableChangeCounts = [],
        array $insertedCoinCounts = [],
    ): Machine {
        $quantities = array_replace(
            [
                'water' => 10,
                'juice' => 8,
                'soda' => 5,
            ],
            $stockQuantities,
        );

        return Machine::initialize(
            [
                $this->productStock('water', 'Water', 65, $quantities['water']),
                $this->productStock('juice', 'Juice', 100, $quantities['juice']),
                $this->productStock('soda', 'Soda', 150, $quantities['soda']),
            ],
            AvailableChange::fromCounts($availableChangeCounts),
            InsertedCoins::fromCounts($insertedCoinCounts),
        );
    }

    protected function machineFailureFactory(): MachineFailureFactory
    {
        return new MachineFailureFactory();
    }

    protected function machineSnapshotFactory(): MachineSnapshotFactory
    {
        return new MachineSnapshotFactory();
    }

    protected function repository(
        ?Machine $machine = null,
        string $machineId = 'default',
    ): InMemoryMachineRepository {
        if ($machine === null) {
            return new InMemoryMachineRepository();
        }

        return new InMemoryMachineRepository([$machineId => $machine]);
    }

    private function productStock(
        string $selector,
        string $name,
        int $priceCents,
        int $quantity,
    ): ProductStock {
        return new ProductStock(
            new Product(
                Selector::fromString($selector),
                Money::fromCents($priceCents),
                $name,
            ),
            $quantity,
        );
    }
}
