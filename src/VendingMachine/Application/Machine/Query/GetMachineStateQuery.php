<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Query;

use InvalidArgumentException;

/**
 * Requests the current machine snapshot without mutating state.
 */
final readonly class GetMachineStateQuery
{
    private string $machineId;

    public function __construct(
        string $machineId = 'default',
    ) {
        $this->machineId = self::normalizeMachineId($machineId);
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
