<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;

/**
 * Carries the input required to insert one supported coin into a machine.
 */
final readonly class InsertCoinCommand
{
    private string $machineId;

    public function __construct(
        private int $coinCents,
        string $machineId = 'default',
    ) {
        $this->machineId = self::normalizeMachineId($machineId);

        if ($this->coinCents <= 0) {
            throw new InvalidArgumentException('Insert coin amount must be greater than zero.');
        }
    }

    public function coinCents(): int
    {
        return $this->coinCents;
    }

    public function machineId(): string
    {
        return $this->machineId;
    }

    private static function normalizeMachineId(string $machineId): string
    {
        $normalized = strtolower(trim($machineId));

        if ($normalized === '') {
            throw new InvalidArgumentException('Machine id cannot be empty.');
        }

        return $normalized;
    }
}
