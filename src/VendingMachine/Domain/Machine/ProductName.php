<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

/**
 * Represents the display name of a vendable product.
 */
final readonly class ProductName implements \Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        return new self($normalized);
    }

    public static function from(self|string $value): self
    {
        return $value instanceof self
            ? $value
            : self::fromString($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
