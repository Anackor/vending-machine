<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Repository;

use VendingMachine\Domain\Machine\Machine;

/**
 * Application port for loading and saving the machine aggregate.
 */
interface MachineRepository
{
    /**
     * Load one machine aggregate by its logical identifier.
     */
    public function find(string $machineId): ?Machine;

    /**
     * Persist the full aggregate as one logical replacement.
     *
     * Implementations are expected to make the update of a single machine
     * aggregate appear atomic from the handler perspective.
     */
    public function save(string $machineId, Machine $machine): void;
}
