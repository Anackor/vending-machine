<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Query;

use VendingMachine\Domain\Machine\MachineId;

/**
 * Requests the current machine snapshot without mutating state.
 */
final readonly class GetMachineStateQuery
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
