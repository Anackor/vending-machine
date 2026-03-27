<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Repository;

use VendingMachine\Domain\Machine\Machine;

interface MachineRepository
{
    public function find(string $machineId): ?Machine;

    /**
     * Persist the full aggregate as one logical replacement.
     *
     * Implementations are expected to make the update of a single machine
     * aggregate appear atomic from the handler perspective.
     */
    public function save(string $machineId, Machine $machine): void;
}
