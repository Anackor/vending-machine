<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\ValueObject;

/**
 * Wraps the machine-owned coin inventory that can be used to return change.
 */
final readonly class AvailableChange
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

    /**
     * @param self|array<int|string, mixed> $value
     */
    public static function from(self|array $value): self
    {
        return $value instanceof self
            ? $value
            : self::fromCounts($value);
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

    public function addInsertedCoins(InsertedCoins $insertedCoins): self
    {
        return new self($this->coins->add($insertedCoins->toCoinInventory()));
    }

    public function remove(CoinInventory $coins): self
    {
        return new self($this->coins->subtract($coins));
    }

    public function allocateChange(Money $amount): ?CoinInventory
    {
        return $this->coins->allocateForAmount($amount);
    }

    public function toCoinInventory(): CoinInventory
    {
        return $this->coins;
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
