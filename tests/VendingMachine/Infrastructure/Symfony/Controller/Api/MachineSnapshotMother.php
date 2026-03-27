<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Controller\Api;

use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;

final class MachineSnapshotMother
{
    /**
     * @param array<int, int> $availableChangeCounts
     * @param array<int, int> $insertedCoins
     */
    public static function create(
        int $insertedBalanceCents = 0,
        array $insertedCoins = [],
        array $availableChangeCounts = [5 => 20, 10 => 20, 25 => 20, 100 => 10],
    ): MachineSnapshot {
        return new MachineSnapshot(
            'default',
            $insertedBalanceCents,
            $insertedCoins,
            $availableChangeCounts,
            [
                new ProductSnapshot('juice', 100, 8, 'Juice'),
                new ProductSnapshot('soda', 150, 5, 'Soda'),
                new ProductSnapshot('water', 65, 10, 'Water'),
            ],
        );
    }
}
