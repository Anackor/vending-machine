<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;

/**
 * Persistence DTO that stores one complete machine aggregate as a single MongoDB document.
 */
final readonly class MachineDocument
{
    private string $machineId;

    /**
     * @var list<ProductStockDocument>
     */
    private array $productStocks;

    /**
     * @var array<int, int>
     */
    private array $availableChangeCounts;

    /**
     * @var array<int, int>
     */
    private array $insertedCoinCounts;

    /**
     * @param list<ProductStockDocument> $productStocks
     * @param array<int|string, mixed> $availableChangeCounts
     * @param array<int|string, mixed> $insertedCoinCounts
     */
    public function __construct(
        string $machineId,
        array $productStocks,
        array $availableChangeCounts,
        array $insertedCoinCounts,
    ) {
        $machineId = trim($machineId);

        if ($machineId === '') {
            throw new InvalidArgumentException('Persisted machine id cannot be empty.');
        }

        if ($productStocks === []) {
            throw new InvalidArgumentException('Persisted machine document must contain product stocks.');
        }

        $selectors = [];

        foreach ($productStocks as $productStock) {
            $selector = $productStock->selector()->value();

            if (isset($selectors[$selector])) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate persisted product selector "%s" detected.',
                    $selector,
                ));
            }

            $selectors[$selector] = true;
        }

        $this->machineId = $machineId;
        $this->productStocks = $productStocks;
        $this->availableChangeCounts = self::normalizeCoinCounts(
            $availableChangeCounts,
            'Persisted available change counts',
        );
        $this->insertedCoinCounts = self::normalizeCoinCounts(
            $insertedCoinCounts,
            'Persisted inserted coin counts',
        );
    }

    public function machineId(): string
    {
        return $this->machineId;
    }

    /**
     * @return list<ProductStockDocument>
     */
    public function productStocks(): array
    {
        return $this->productStocks;
    }

    /**
     * @return array<int, int>
     */
    public function availableChangeCounts(): array
    {
        return $this->availableChangeCounts;
    }

    /**
     * @return array<int, int>
     */
    public function insertedCoinCounts(): array
    {
        return $this->insertedCoinCounts;
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
                throw new InvalidArgumentException(sprintf(
                    '%s must use integer denomination keys.',
                    $label,
                ));
            }

            if ($count === 0) {
                continue;
            }

            $normalized[(int) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }
}
