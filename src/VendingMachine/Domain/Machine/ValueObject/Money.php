<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\ValueObject;

use InvalidArgumentException;

/**
 * Represents money as integer cents to avoid floating-point precision issues.
 */
final readonly class Money
{
    private function __construct(
        private int $cents,
    ) {
        if ($this->cents < 0) {
            throw new InvalidArgumentException('Money cents cannot be negative.');
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        $remaining = $this->cents - $other->cents;

        if ($remaining < 0) {
            throw new InvalidArgumentException('Money subtraction cannot produce a negative amount.');
        }

        return new self($remaining);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }
}
