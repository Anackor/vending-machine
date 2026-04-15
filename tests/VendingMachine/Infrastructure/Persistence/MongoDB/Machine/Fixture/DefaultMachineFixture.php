<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture;

use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;

final class DefaultMachineFixture
{
    /**
     * @param array<string, int> $stockQuantities
     * @param array<int|string, mixed> $availableChangeCounts
     * @param array<int|string, mixed> $insertedCoinCounts
     */
    public static function machine(
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
                self::productStock('water', 'Water', 65, $quantities['water']),
                self::productStock('juice', 'Juice', 100, $quantities['juice']),
                self::productStock('soda', 'Soda', 150, $quantities['soda']),
            ],
            AvailableChange::fromCounts($availableChangeCounts),
            InsertedCoins::fromCounts($insertedCoinCounts),
        );
    }

    /**
     * @param array<string, int> $stockQuantities
     * @param array<int|string, mixed> $availableChangeCounts
     * @param array<int|string, mixed> $insertedCoinCounts
     *
     * @return array<string, mixed>
     */
    public static function document(
        array $stockQuantities = [],
        array $availableChangeCounts = [],
        array $insertedCoinCounts = [],
        string $machineId = 'default',
    ): array {
        $quantities = array_replace(
            [
                'water' => 10,
                'juice' => 8,
                'soda' => 5,
            ],
            $stockQuantities,
        );

        return [
            '_id' => $machineId,
            'products' => [
                [
                    'selector' => 'water',
                    'name' => 'Water',
                    'priceCents' => 65,
                    'quantity' => $quantities['water'],
                ],
                [
                    'selector' => 'juice',
                    'name' => 'Juice',
                    'priceCents' => 100,
                    'quantity' => $quantities['juice'],
                ],
                [
                    'selector' => 'soda',
                    'name' => 'Soda',
                    'priceCents' => 150,
                    'quantity' => $quantities['soda'],
                ],
            ],
            'availableChange' => self::normalizeCounts($availableChangeCounts),
            'insertedCoins' => self::normalizeCounts($insertedCoinCounts),
        ];
    }

    /**
     * @param array<int|string, mixed> $counts
     *
     * @return array<string, mixed>
     */
    private static function normalizeCounts(array $counts): array
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            $normalized[(string) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }

    private static function productStock(
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
