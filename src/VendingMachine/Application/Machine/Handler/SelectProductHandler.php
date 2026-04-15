<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;

/**
 * Coordinates product selection, purchase rules, and the resulting state persistence.
 */
final readonly class SelectProductHandler
{
    public function __construct(
        private MachineRepository $machineRepository,
        private MachineSnapshotFactory $machineSnapshotFactory,
        private MachineFailureFactory $machineFailureFactory,
    ) {
    }

    public function handle(SelectProductCommand $command): SelectProductResult
    {
        // The handler keeps orchestration outside the domain while the aggregate owns all purchase rules.
        $machine = $this->machineRepository->find($command->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($command->machineId());
        }

        try {
            $purchase = $machine->purchase($command->selector());
        } catch (ExactChangeNotAvailable | InsufficientBalance | ProductNotFound | ProductOutOfStock $exception) {
            throw $this->machineFailureFactory->fromDomainThrowable(
                $command->machineId(),
                $exception,
                ['selector' => $command->selector()->value()],
            );
        }

        $updatedMachine = $purchase->machine();
        $this->machineRepository->save($command->machineId(), $updatedMachine);

        return new SelectProductResult(
            $purchase->product()->selector(),
            $purchase->product()->name(),
            $purchase->change()->counts(),
            $this->machineSnapshotFactory->create($command->machineId(), $updatedMachine),
        );
    }
}
