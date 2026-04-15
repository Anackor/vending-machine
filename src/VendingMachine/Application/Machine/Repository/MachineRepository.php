<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Repository;

use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\MachineId;

/**
 * Application port for loading and saving the machine aggregate.
 */
interface MachineRepository
{
    /**
     * Load one machine aggregate by its logical identifier.
     */
    public function find(MachineId $machineId): ?Machine;

    /**
     * Persist the full aggregate as one logical replacement.
     *
     * Implementations are expected to make the update of a single machine
     * aggregate appear atomic from the handler perspective.
     */
    public function save(MachineId $machineId, Machine $machine): void;
}
