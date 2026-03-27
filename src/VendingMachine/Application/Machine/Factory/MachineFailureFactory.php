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

final class MachineFailureFactory
{
    public function machineNotFound(string $machineId): MachineOperationFailed
    {
        return $this->build(
            MachineFailureCode::MachineNotFound,
            sprintf('Machine "%s" was not found.', $machineId),
            ['machineId' => $machineId],
        );
    }

    public function unsupportedCoin(
        string $machineId,
        int $coinCents,
        InvalidArgumentException $exception,
    ): MachineOperationFailed {
        return $this->build(
            MachineFailureCode::UnsupportedCoin,
            $exception->getMessage(),
            [
                'coinCents' => $coinCents,
                'machineId' => $machineId,
            ],
        );
    }

    public function invalidProductSelection(
        string $machineId,
        string $selector,
        InvalidArgumentException $exception,
    ): MachineOperationFailed {
        return $this->build(
            MachineFailureCode::ProductNotFound,
            $exception->getMessage(),
            [
                'machineId' => $machineId,
                'selector' => $selector,
            ],
        );
    }

    public function invalidServiceConfiguration(
        string $machineId,
        InvalidArgumentException $exception,
    ): MachineOperationFailed {
        return $this->build(
            MachineFailureCode::InvalidServiceConfiguration,
            $exception->getMessage(),
            ['machineId' => $machineId],
        );
    }

    /**
     * @param array<string, bool|float|int|string> $context
     */
    public function fromDomainThrowable(
        string $machineId,
        Throwable $throwable,
        array $context = [],
    ): MachineOperationFailed {
        $context['machineId'] = $machineId;

        return $this->build(
            $this->mapCode($throwable),
            $throwable->getMessage(),
            $context,
        );
    }

    private function mapCode(Throwable $throwable): MachineFailureCode
    {
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
