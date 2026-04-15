<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\InvalidServiceConfiguration;
use VendingMachine\Domain\Machine\Exception\PendingBalanceDuringService;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;
use VendingMachine\Domain\Machine\ValueObject\MachineId;

final class MachineFailureFactoryTest extends TestCase
{
    public function testItBuildsTheMissingMachineFailure(): void
    {
        $failure = new MachineFailureFactory()->machineNotFound(MachineId::default());

        $this->assertMachineFailure(
            $failure,
            MachineFailureCode::MachineNotFound,
            'Machine "default" was not found.',
            ['machineId' => 'default'],
        );
    }

    public function testItBuildsTheUnsupportedCoinFailure(): void
    {
        $failure = new MachineFailureFactory()->unsupportedCoin(
            MachineId::default(),
            50,
            new InvalidArgumentException('Unsupported coin denomination "50".'),
        );

        $this->assertMachineFailure(
            $failure,
            MachineFailureCode::UnsupportedCoin,
            'Unsupported coin denomination "50".',
            [
                'coinCents' => 50,
                'machineId' => 'default',
            ],
        );
    }

    public function testItBuildsTheInvalidServiceConfigurationFailure(): void
    {
        $failure = new MachineFailureFactory()->invalidServiceConfiguration(
            MachineId::default(),
            new InvalidArgumentException('Missing stock count for selector "juice".'),
        );

        $this->assertMachineFailure(
            $failure,
            MachineFailureCode::InvalidServiceConfiguration,
            'Missing stock count for selector "juice".',
            ['machineId' => 'default'],
        );
    }

    public function testItMapsDomainFailuresIntoApplicationFailures(): void
    {
        $factory = new MachineFailureFactory();

        $cases = [
            [
                new ExactChangeNotAvailable('Exact change "35" cannot be returned for selector "water".'),
                MachineFailureCode::ExactChangeUnavailable,
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            ],
            [
                new InsufficientBalance('Inserted balance "25" is insufficient for product price "65".'),
                MachineFailureCode::InsufficientBalance,
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            ],
            [
                new InvalidServiceConfiguration('Missing stock count for selector "juice".'),
                MachineFailureCode::InvalidServiceConfiguration,
                ['machineId' => 'default'],
            ],
            [
                new PendingBalanceDuringService('Machine service requires no pending customer balance.'),
                MachineFailureCode::PendingBalanceDuringService,
                ['machineId' => 'default'],
            ],
            [
                new ProductNotFound('Unknown selector "chips".'),
                MachineFailureCode::ProductNotFound,
                [
                    'machineId' => 'default',
                    'selector' => 'chips',
                ],
            ],
            [
                new ProductOutOfStock('Product "Water" is out of stock.'),
                MachineFailureCode::ProductOutOfStock,
                [
                    'machineId' => 'default',
                    'selector' => 'water',
                ],
            ],
        ];

        foreach ($cases as [$throwable, $code, $context]) {
            $failure = $factory->fromDomainThrowable(
                MachineId::default(),
                $throwable,
                array_diff_key($context, ['machineId' => true]),
            );

            $this->assertMachineFailure(
                $failure,
                $code,
                $throwable->getMessage(),
                $context,
            );
        }
    }

    public function testItRejectsUnsupportedDomainFailures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unsupported domain failure "RuntimeException" for application failure mapping.',
        );

        new MachineFailureFactory()->fromDomainThrowable(
            MachineId::default(),
            new RuntimeException('Unexpected failure.'),
        );
    }

    /**
     * @param array<string, bool|float|int|string> $context
     */
    private function assertMachineFailure(
        MachineOperationFailed $failure,
        MachineFailureCode $code,
        string $message,
        array $context,
    ): void {
        self::assertSame($code, $failure->failure()->code());
        self::assertSame($message, $failure->failure()->message());
        self::assertSame($context, $failure->failure()->context());
        self::assertSame($message, $failure->getMessage());
    }
}
