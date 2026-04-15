<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Command;

use VendingMachine\Domain\Machine\ValueObject\MachineId;

/**
 * Carries the input required to refund the current customer balance.
 */
final readonly class ReturnInsertedMoneyCommand
{
    private MachineId $machineId;

    public function __construct(
        MachineId|string $machineId = 'default',
    ) {
        $this->machineId = MachineId::from($machineId);
    }

    public function machineId(): MachineId
    {
        return $this->machineId;
    }
}
