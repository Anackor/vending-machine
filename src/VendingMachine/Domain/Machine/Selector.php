<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

/**
 * Normalizes the canonical selector used to address products across the system.
 */
final readonly class Selector implements \Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            throw new InvalidArgumentException('Selector cannot be empty.');
        }

        if (preg_match('/^[a-z][a-z0-9-]*$/', $normalized) !== 1) {
            throw new InvalidArgumentException(sprintf('Selector "%s" is invalid.', $value));
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
