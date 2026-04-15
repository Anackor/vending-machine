<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Factory;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;

final class MachineSnapshotFactoryTest extends TestCase
{
    public function testItBuildsAStableApplicationSnapshotFromTheMachineAggregate(): void
    {
        $factory = new MachineSnapshotFactory();
        $snapshot = $factory->create(MachineId::fromString(' DEFAULT '), $this->machine());
        $water = $snapshot->productSnapshotFor('water');
        $juice = $snapshot->productSnapshotFor('juice');
        $soda = $snapshot->productSnapshotFor('soda');

        self::assertSame('default', $snapshot->machineId()->value());
        self::assertSame(110, $snapshot->insertedBalanceCents());
        self::assertSame([10 => 1, 100 => 1], $snapshot->insertedCoins());
        self::assertSame([5 => 4, 25 => 2], $snapshot->availableChangeCounts());
        self::assertCount(3, $snapshot->products());

        self::assertNotNull($water);
        self::assertNotNull($juice);
        self::assertNotNull($soda);

        self::assertSame('water', $water->selector());
        self::assertSame('Water', $water->name());
        self::assertSame(65, $water->priceCents());
        self::assertSame(3, $water->quantity());

        self::assertSame('juice', $juice->selector());
        self::assertSame('Juice', $juice->name());
        self::assertSame(100, $juice->priceCents());
        self::assertSame(0, $juice->quantity());

        self::assertSame('soda', $soda->selector());
        self::assertSame('Soda', $soda->name());
        self::assertSame(150, $soda->priceCents());
        self::assertSame(1, $soda->quantity());
    }

    private function machine(): Machine
    {
        return Machine::initialize(
            [
                $this->productStock('water', 'Water', 65, 3),
                $this->productStock('juice', 'Juice', 100, 0),
                $this->productStock('soda', 'Soda', 150, 1),
            ],
            AvailableChange::fromCounts([25 => 2, 5 => 4]),
            InsertedCoins::fromCounts([100 => 1, 10 => 1]),
        );
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
