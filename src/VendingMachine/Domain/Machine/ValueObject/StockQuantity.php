<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\ValueObject;

use InvalidArgumentException;

/**
 * Represents the non-negative stock available for one product.
 */
final readonly class StockQuantity
{
    private function __construct(
        private int $value,
    ) {
        if ($this->value < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function from(self|int $value): self
    {
        return $value instanceof self
            ? $value
            : self::fromInt($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function decrement(): self
    {
        return new self($this->value - 1);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isAvailable(): bool
    {
        return $this->value > 0;
    }
}
