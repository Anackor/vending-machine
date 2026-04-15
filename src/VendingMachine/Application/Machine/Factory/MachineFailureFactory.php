<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Factory;

use InvalidArgumentException;
use Throwable;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailure;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\InvalidServiceConfiguration;
use VendingMachine\Domain\Machine\Exception\PendingBalanceDuringService;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;
use VendingMachine\Domain\Machine\MachineId;

/**
 * Translates domain and orchestration errors into stable application failures.
 */
final class MachineFailureFactory
{
    public function machineNotFound(MachineId $machineId): MachineOperationFailed
    {
        return $this->build(
            MachineFailureCode::MachineNotFound,
            sprintf('Machine "%s" was not found.', $machineId),
            ['machineId' => $machineId->value()],
        );
    }

    public function unsupportedCoin(
        MachineId $machineId,
        int $coinCents,
        InvalidArgumentException $exception,
    ): MachineOperationFailed {
        return $this->build(
            MachineFailureCode::UnsupportedCoin,
            $exception->getMessage(),
            [
                'coinCents' => $coinCents,
                'machineId' => $machineId->value(),
            ],
        );
    }

    public function invalidServiceConfiguration(
        MachineId $machineId,
        InvalidArgumentException $exception,
    ): MachineOperationFailed {
        return $this->build(
            MachineFailureCode::InvalidServiceConfiguration,
            $exception->getMessage(),
            ['machineId' => $machineId->value()],
        );
    }

    /**
     * @param array<string, bool|float|int|string> $context
     */
    public function fromDomainThrowable(
        MachineId $machineId,
        Throwable $throwable,
        array $context = [],
    ): MachineOperationFailed {
        // Handlers add use-case context here while preserving a stable public error contract.
        $context['machineId'] = $machineId->value();

        return $this->build(
            $this->mapCode($throwable),
            $throwable->getMessage(),
            $context,
        );
    }

    private function mapCode(Throwable $throwable): MachineFailureCode
    {
        // Mapping stays explicit so adapters never need to know domain exception classes.
        return match (true) {
            $throwable instanceof ExactChangeNotAvailable => MachineFailureCode::ExactChangeUnavailable,
            $throwable instanceof InsufficientBalance => MachineFailureCode::InsufficientBalance,
            $throwable instanceof InvalidServiceConfiguration => MachineFailureCode::InvalidServiceConfiguration,
            $throwable instanceof PendingBalanceDuringService => MachineFailureCode::PendingBalanceDuringService,
            $throwable instanceof ProductNotFound => MachineFailureCode::ProductNotFound,
            $throwable instanceof ProductOutOfStock => MachineFailureCode::ProductOutOfStock,
            default => throw new InvalidArgumentException(sprintf(
                'Unsupported domain failure "%s" for application failure mapping.',
                $throwable::class,
            )),
        };
    }

    /**
     * @param array<string, bool|float|int|string> $context
     */
    private function build(
        MachineFailureCode $code,
        string $message,
        array $context,
    ): MachineOperationFailed {
        return new MachineOperationFailed(
            new MachineFailure($code, $message, $context),
        );
    }
}
