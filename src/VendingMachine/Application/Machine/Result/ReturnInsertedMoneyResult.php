<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Result;

use InvalidArgumentException;

/**
 * Wraps the returned coin counts and the machine snapshot after a refund.
 */
final readonly class ReturnInsertedMoneyResult
{
    /**
     * @var array<int, int>
     */
    private array $returnedCoinCounts;

    /**
     * @param array<int|string, mixed> $returnedCoinCounts
     */
    public function __construct(
        array $returnedCoinCounts,
        private MachineSnapshot $machineSnapshot,
    ) {
        $this->returnedCoinCounts = self::normalizeCoinCounts($returnedCoinCounts);
    }

    public function machineSnapshot(): MachineSnapshot
    {
        return $this->machineSnapshot;
    }

    /**
     * @return array<int, int>
     */
    public function returnedCoinCounts(): array
    {
        return $this->returnedCoinCounts;
    }

    /**
     * @param array<int|string, mixed> $counts
     *
     * @return array<int, int>
     */
    private static function normalizeCoinCounts(array $counts): array
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            if (!is_int($count)) {
                throw new InvalidArgumentException('Returned coin counts must be integers.');
            }

            if ($count < 0) {
                throw new InvalidArgumentException('Returned coin counts cannot be negative.');
            }

            if (is_string($denomination) && !ctype_digit($denomination)) {
                throw new InvalidArgumentException('Returned coin denomination keys must be integer values.');
            }

            $normalized[(int) $denomination] = $count;
        }

        ksort($normalized);

        return $normalized;
    }
}
