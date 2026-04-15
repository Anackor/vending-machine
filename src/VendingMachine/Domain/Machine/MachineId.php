<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

/**
 * Identifies one logical vending machine across application and persistence boundaries.
 */
final readonly class MachineId implements \Stringable
{
    private const string DEFAULT = 'default';

    private function __construct(
        private string $value,
    ) {
    }

    public static function default(): self
    {
        return new self(self::DEFAULT);
    }

    public static function from(self|string $value): self
    {
        return $value instanceof self
            ? $value
            : self::fromString($value);
    }

    public static function fromString(string $value): self
    {
        return new self(self::normalize($value));
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

    private static function normalize(string $value): string
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            throw new InvalidArgumentException('Machine id cannot be empty.');
        }

        return $normalized;
    }
}
