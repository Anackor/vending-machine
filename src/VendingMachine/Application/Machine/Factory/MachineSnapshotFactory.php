<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Factory;

use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;
use VendingMachine\Domain\Machine\Machine;

final class MachineSnapshotFactory
{
    public function create(string $machineId, Machine $machine): MachineSnapshot
    {
        $products = [];

        foreach ($machine->productStocks() as $productStock) {
            $product = $productStock->product();

            $products[] = new ProductSnapshot(
                $product->selector()->value(),
                $product->price()->cents(),
                $productStock->quantity(),
                $product->name(),
            );
        }

        return new MachineSnapshot(
            $machineId,
            $machine->insertedBalance()->cents(),
            $machine->insertedCoins()->counts(),
            $machine->availableChange()->counts(),
            $products,
        );
    }
}
