<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\ValueObject\MachineId;
use VendingMachine\Domain\Machine\ValueObject\Selector;

/**
 * Flat application snapshot of the machine state exposed to adapters.
 */
final readonly class MachineSnapshot
{
    /**
     * @var array<int, int>
     */
    private array $availableChangeCounts;

    /**
     * @var array<int, int>
     */
    private array $insertedCoins;

    private MachineId $machineId;

    /**
     * @var array<string, ProductSnapshot>
     */
    private array $productsBySelector;

    /**
     * @param array<int|string, mixed> $availableChangeCounts
     * @param array<int|string, mixed> $insertedCoins
     * @param list<ProductSnapshot> $products
     */
    public function __construct(
        MachineId|string $machineId,
        private int $insertedBalanceCents,
        array $insertedCoins,
        array $availableChangeCounts,
        array $products,
    ) {
        $this->machineId = MachineId::from($machineId);

        if ($this->insertedBalanceCents < 0) {
            throw new InvalidArgumentException('Inserted balance cents cannot be negative.');
        }

        $this->insertedCoins = self::normalizeCoinCounts($insertedCoins, 'Inserted coin counts');
        $this->availableChangeCounts = self::normalizeCoinCounts($availableChangeCounts, 'Available change counts');
        $this->productsBySelector = self::indexProducts($products);
    }

    /**
     * @return array<int, int>
     */
    public function availableChangeCounts(): array
    {
        return $this->availableChangeCounts;
    }

    public function hasPendingBalance(): bool
    {
        return $this->insertedBalanceCents > 0;
    }

    public function insertedBalanceCents(): int
    {
        return $this->insertedBalanceCents;
    }

    /**
     * @return array<int, int>
     */
    public function insertedCoins(): array
    {
        return $this->insertedCoins;
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }

    public function productSnapshotFor(Selector|string $selector): ?ProductSnapshot
    {
        return $this->productsBySelector[Selector::from($selector)->value()] ?? null;
    }

    /**
     * @return list<ProductSnapshot>
     */
    public function products(): array
    {
        return array_values($this->productsBySelector);
    }

    /**
     * @param array<int|string, mixed> $counts
     *
     * @return array<int, int>
     */
    private static function normalizeCoinCounts(array $counts, string $label): array
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            if (!is_int($count)) {
                throw new InvalidArgumentException(sprintf('%s must be integers.', $label));
            }

            if ($count < 0) {
                throw new InvalidArgumentException(sprintf('%s cannot be negative.', $label));
            }

            if (is_string($denomination) && !ctype_digit($denomination)) {
                throw new InvalidArgumentException(sprintf('%s must use integer denomination keys.', $label));
            }

            $normalized[(int) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param list<ProductSnapshot> $products
     *
     * @return array<string, ProductSnapshot>
     */
    private static function indexProducts(array $products): array
    {
        if ($products === []) {
            throw new InvalidArgumentException('Machine snapshot products cannot be empty.');
        }

        $indexed = [];

        foreach ($products as $product) {
            $selector = $product->selector()->value();

            if (isset($indexed[$selector])) {
                throw new InvalidArgumentException(sprintf('Duplicate product snapshot selector "%s" detected.', $selector));
            }

            $indexed[$selector] = $product;
        }

        ksort($indexed);

        return $indexed;
    }
}
