<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Exception;

use RuntimeException;
use VendingMachine\Application\Machine\Failure\MachineFailure;

/**
 * Exception wrapper used to move stable application failures across adapters.
 */
final class MachineOperationFailed extends RuntimeException
{
    public function __construct(
        private readonly MachineFailure $failure,
    ) {
        parent::__construct($failure->message());
    }

    public function failure(): MachineFailure
    {
        return $this->failure;
    }
}
