<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

final readonly class CoinInventory
{
    /**
     * @param array<int, int> $counts
     */
    private function __construct(
        private array $counts,
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param array<int|string, mixed> $counts
     */
    public static function fromCounts(array $counts): self
    {
        $normalized = [];

        foreach ($counts as $denomination => $count) {
            if (!is_int($count)) {
                throw new InvalidArgumentException('Coin counts must be integers.');
            }

            if ($count < 0) {
                throw new InvalidArgumentException('Coin counts cannot be negative.');
            }

            if ($count === 0) {
                continue;
            }

            $coin = Coin::fromCents(self::normalizeDenomination($denomination));
            $normalized[$coin->value] = $count;
        }

        ksort($normalized);

        return new self($normalized);
    }

    /**
     * @return array<int, int>
     */
    public function counts(): array
    {
        return $this->counts;
    }

    public function countFor(Coin $coin): int
    {
        return $this->counts[$coin->value] ?? 0;
    }

    public function total(): Money
    {
        $totalCents = 0;

        foreach ($this->counts as $denomination => $count) {
            $totalCents += $denomination * $count;
        }

        return Money::fromCents($totalCents);
    }

    public function isEmpty(): bool
    {
        return $this->counts === [];
    }

    private static function normalizeDenomination(int|string $denomination): int
    {
        if (is_string($denomination) && !ctype_digit($denomination)) {
            throw new InvalidArgumentException('Coin denomination keys must be integer values.');
        }

        return (int) $denomination;
    }
}
