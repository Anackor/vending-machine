<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Handler;

use InvalidArgumentException;
use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;
use VendingMachine\Domain\Machine\Selector;

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
        $machine = $this->machineRepository->find($command->machineId());

        if ($machine === null) {
            throw $this->machineFailureFactory->machineNotFound($command->machineId());
        }

        try {
            $purchase = $machine->purchase(Selector::fromString($command->selector()));
        } catch (InvalidArgumentException $exception) {
            throw $this->machineFailureFactory->invalidProductSelection(
                $command->machineId(),
                $command->selector(),
                $exception,
            );
        } catch (ExactChangeNotAvailable | InsufficientBalance | ProductNotFound | ProductOutOfStock $exception) {
            throw $this->machineFailureFactory->fromDomainThrowable(
                $command->machineId(),
                $exception,
                ['selector' => $command->selector()],
            );
        }

        $updatedMachine = $purchase->machine();
        $this->machineRepository->save($command->machineId(), $updatedMachine);

        return new SelectProductResult(
            $purchase->product()->selector()->value(),
            $purchase->product()->name(),
            $purchase->change()->counts(),
            $this->machineSnapshotFactory->create($command->machineId(), $updatedMachine),
        );
    }
}
