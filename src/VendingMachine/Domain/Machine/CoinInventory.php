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

    public function addCoin(Coin $coin): self
    {
        $counts = $this->counts;
        $counts[$coin->value] = ($counts[$coin->value] ?? 0) + 1;
        ksort($counts);

        return new self($counts);
    }

    public function add(self $other): self
    {
        $counts = $this->counts;

        foreach ($other->counts() as $denomination => $count) {
            $counts[$denomination] = ($counts[$denomination] ?? 0) + $count;
        }

        ksort($counts);

        return new self($counts);
    }

    public function subtract(self $other): self
    {
        $counts = $this->counts;

        foreach ($other->counts() as $denomination => $count) {
            $currentCount = $counts[$denomination] ?? 0;

            if ($currentCount < $count) {
                throw new InvalidArgumentException('Coin inventory subtraction cannot produce negative counts.');
            }

            $remainingCount = $currentCount - $count;

            if ($remainingCount === 0) {
                unset($counts[$denomination]);

                continue;
            }

            $counts[$denomination] = $remainingCount;
        }

        ksort($counts);

        return new self($counts);
    }

    public function allocateForAmount(Money $amount): ?self
    {
        if ($amount->isZero()) {
            return self::empty();
        }

        $allocatedCounts = self::findChangeCounts(
            $amount->cents(),
            array_reverse(array_map(static fn (Coin $coin): int => $coin->value, Coin::cases())),
            $this->counts,
            0,
        );

        return $allocatedCounts === null ? null : new self($allocatedCounts);
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

    /**
     * @param list<int> $denominations
     * @param array<int, int> $availableCounts
     *
     * @return array<int, int>|null
     */
    private static function findChangeCounts(
        int $remainingAmount,
        array $denominations,
        array $availableCounts,
        int $index,
    ): ?array {
        if ($remainingAmount === 0) {
            return [];
        }

        if ($index >= count($denominations)) {
            return null;
        }

        $denomination = $denominations[$index];
        $availableCount = $availableCounts[$denomination] ?? 0;
        $maxUsableCoins = min(intdiv($remainingAmount, $denomination), $availableCount);

        for ($coinsToUse = $maxUsableCoins; $coinsToUse >= 0; --$coinsToUse) {
            $nextAvailableCounts = $availableCounts;

            if ($coinsToUse > 0) {
                $nextAvailableCounts[$denomination] -= $coinsToUse;

                if ($nextAvailableCounts[$denomination] === 0) {
                    unset($nextAvailableCounts[$denomination]);
                }
            }

            $nextResult = self::findChangeCounts(
                $remainingAmount - ($coinsToUse * $denomination),
                $denominations,
                $nextAvailableCounts,
                $index + 1,
            );

            if ($nextResult === null) {
                continue;
            }

            if ($coinsToUse > 0) {
                $nextResult[$denomination] = $coinsToUse;
                ksort($nextResult);
            }

            return $nextResult;
        }

        return null;
    }

    private static function normalizeDenomination(int|string $denomination): int
    {
        if (is_string($denomination) && !ctype_digit($denomination)) {
            throw new InvalidArgumentException('Coin denomination keys must be integer values.');
        }

        return (int) $denomination;
    }
}
