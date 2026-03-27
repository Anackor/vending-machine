<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

final readonly class InsertedCoins
{
    public function __construct(
        private CoinInventory $coins,
    ) {
    }

    /**
     * @param array<int|string, mixed> $counts
     */
    public static function fromCounts(array $counts): self
    {
        return new self(CoinInventory::fromCounts($counts));
    }

    public static function empty(): self
    {
        return new self(CoinInventory::empty());
    }

    /**
     * @return array<int, int>
     */
    public function counts(): array
    {
        return $this->coins->counts();
    }

    public function countFor(Coin $coin): int
    {
        return $this->coins->countFor($coin);
    }

    public function total(): Money
    {
        return $this->coins->total();
    }

    public function isEmpty(): bool
    {
        return $this->coins->isEmpty();
    }
}
