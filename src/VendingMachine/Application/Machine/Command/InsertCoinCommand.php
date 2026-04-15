<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use InvalidArgumentException;
use VendingMachine\Domain\Machine\MachineId;

/**
 * Carries the input required to insert one supported coin into a machine.
 */
final readonly class InsertCoinCommand
{
    private MachineId $machineId;

    public function __construct(
        private int $coinCents,
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);

        if ($this->coinCents <= 0) {
            throw new InvalidArgumentException('Insert coin amount must be greater than zero.');
        }
    }

    public function coinCents(): int
    {
        return $this->coinCents;
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }
}
