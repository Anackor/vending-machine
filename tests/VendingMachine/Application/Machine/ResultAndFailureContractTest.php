<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Application\Machine\Exception\MachineOperationFailed;
use VendingMachine\Application\Machine\Failure\MachineFailure;
use VendingMachine\Application\Machine\Failure\MachineFailureCode;
use VendingMachine\Application\Machine\Result\GetMachineStateResult;
use VendingMachine\Application\Machine\Result\InsertCoinResult;
use VendingMachine\Application\Machine\Result\MachineSnapshot;
use VendingMachine\Application\Machine\Result\ProductSnapshot;
use VendingMachine\Application\Machine\Result\ReturnInsertedMoneyResult;
use VendingMachine\Application\Machine\Result\SelectProductResult;
use VendingMachine\Application\Machine\Result\ServiceMachineResult;

final class ResultAndFailureContractTest extends TestCase
{
    public function testItBuildsAMachineSnapshotWithNormalizedProducts(): void
    {
        $snapshot = new MachineSnapshot(
            ' DEFAULT ',
            25,
            ['25' => 1],
            [5 => 2],
            [
                new ProductSnapshot(' WATER ', 65, 2, ' Water '),
                new ProductSnapshot('juice', 100, 0, 'Juice'),
            ],
        );

        $water = $snapshot->productSnapshotFor('water');
        $juice = $snapshot->productSnapshotFor('juice');

        self::assertNotNull($water);
        self::assertNotNull($juice);
        self::assertSame('default', $snapshot->machineId()->value());
        self::assertSame(25, $snapshot->insertedBalanceCents());
        self::assertSame([25 => 1], $snapshot->insertedCoins());
        self::assertSame([5 => 2], $snapshot->availableChangeCounts());
        self::assertTrue($snapshot->hasPendingBalance());
        self::assertCount(2, $snapshot->products());
        self::assertSame('water', $water->selector());
        self::assertTrue($water->isAvailable());
        self::assertFalse($juice->isAvailable());
    }

    public function testItExposesProductSnapshotGetters(): void
    {
        $product = new ProductSnapshot(' WATER ', 65, 2, ' Water ');

        self::assertSame('water', $product->selector());
        self::assertSame('Water', $product->name());
        self::assertSame(65, $product->priceCents());
        self::assertSame(2, $product->quantity());
        self::assertTrue($product->isAvailable());
    }

    public function testItBuildsTheUseCaseResultsAroundTheSharedSnapshot(): void
    {
        $snapshot = $this->snapshot();

        $insertCoin = new InsertCoinResult($snapshot);
        $selectProduct = new SelectProductResult('water', 'Water', [10 => 1], $snapshot);
        $returnInsertedMoney = new ReturnInsertedMoneyResult([25 => 1], $snapshot);
        $serviceMachine = new ServiceMachineResult($snapshot);
        $getMachineState = new GetMachineStateResult($snapshot);

        self::assertSame($snapshot, $insertCoin->machineSnapshot());
        self::assertSame('water', $selectProduct->dispensedProductSelector());
        self::assertSame('Water', $selectProduct->dispensedProductName());
        self::assertSame([10 => 1], $selectProduct->dispensedChangeCounts());
        self::assertSame($snapshot, $selectProduct->machineSnapshot());
        self::assertSame([25 => 1], $returnInsertedMoney->returnedCoinCounts());
        self::assertSame($snapshot, $returnInsertedMoney->machineSnapshot());
        self::assertSame($snapshot, $serviceMachine->machineSnapshot());
        self::assertSame($snapshot, $getMachineState->machineSnapshot());
    }

    public function testItBuildsTheApplicationFailureModel(): void
    {
        $failure = new MachineFailure(
            MachineFailureCode::UnsupportedCoin,
            'Unsupported coin.',
            [
                'coinCents' => 50,
                'machineId' => 'default',
            ],
        );
        $exception = new MachineOperationFailed($failure);

        self::assertSame('unsupported_coin', MachineFailureCode::UnsupportedCoin->value);
        self::assertSame(MachineFailureCode::UnsupportedCoin, $failure->code());
        self::assertSame('Unsupported coin.', $failure->message());
        self::assertSame(
            [
                'coinCents' => 50,
                'machineId' => 'default',
            ],
            $failure->context(),
        );
        self::assertSame('Unsupported coin.', $exception->getMessage());
        self::assertSame($failure, $exception->failure());
    }

    public function testItReturnsNullForUnknownProductSelectorsInTheSnapshot(): void
    {
        self::assertNull($this->snapshot()->productSnapshotFor('soda'));
    }

    public function testItRejectsInvalidSnapshotShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine snapshot products cannot be empty.');

        new MachineSnapshot('default', 0, [], [], []);
    }

    public function testItRejectsNegativeSnapshotBalances(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inserted balance cents cannot be negative.');

        new MachineSnapshot(
            'default',
            -1,
            [],
            [],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
            ],
        );
    }

    public function testItRejectsEmptySnapshotMachineIds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new MachineSnapshot(
            '   ',
            0,
            [],
            [],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
            ],
        );
    }

    public function testItRejectsDuplicateSnapshotSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate product snapshot selector "water" detected.');

        new MachineSnapshot(
            'default',
            0,
            [],
            [],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
                new ProductSnapshot('water', 65, 2, 'Still Water'),
            ],
        );
    }

    public function testItRejectsInvalidSnapshotCoinShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inserted coin counts must be integers.');

        new MachineSnapshot(
            'default',
            0,
            [25 => '1'],
            [],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
            ],
        );
    }

    public function testItRejectsNegativeAvailableChangeCountsInSnapshots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Available change counts cannot be negative.');

        new MachineSnapshot(
            'default',
            0,
            [],
            [25 => -1],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
            ],
        );
    }

    public function testItRejectsInvalidAvailableChangeDenominationKeysInSnapshots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Available change counts must use integer denomination keys.');

        new MachineSnapshot(
            'default',
            0,
            [],
            ['quarter' => 1],
            [
                new ProductSnapshot('water', 65, 1, 'Water'),
            ],
        );
    }

    public function testItRejectsInvalidProductSnapshotShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product snapshot price must be greater than zero.');

        new ProductSnapshot('water', 0, 1, 'Water');
    }

    public function testItRejectsEmptyProductSnapshotNames(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product snapshot name cannot be empty.');

        new ProductSnapshot('water', 65, 1, '   ');
    }

    public function testItRejectsNegativeProductSnapshotQuantities(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product snapshot quantity cannot be negative.');

        new ProductSnapshot('water', 65, -1, 'Water');
    }

    public function testItRejectsEmptyProductSnapshotSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product snapshot selector cannot be empty.');

        new ProductSnapshot('   ', 65, 1, 'Water');
    }

    public function testItRejectsInvalidFailureMessages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Application failure message cannot be empty.');

        new MachineFailure(MachineFailureCode::MachineNotFound, '   ');
    }

    public function testItRejectsNegativeReturnedCoinCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Returned coin counts cannot be negative.');

        new ReturnInsertedMoneyResult([25 => -1], $this->snapshot());
    }

    public function testItRejectsInvalidResultCoinShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Returned coin denomination keys must be integer values.');

        new ReturnInsertedMoneyResult(['quarter' => 1], $this->snapshot());
    }

    public function testItRejectsNonIntegerReturnedCoinCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Returned coin counts must be integers.');

        new ReturnInsertedMoneyResult([25 => '1'], $this->snapshot());
    }

    public function testItRejectsInvalidSelectProductResultShapes(): void
    {
        $snapshot = $this->snapshot();

        try {
            new SelectProductResult('   ', 'Water', [25 => 1], $snapshot);
            self::fail('The result should have rejected the empty selector.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Dispensed product selector cannot be empty.', $exception->getMessage());
        }

        try {
            new SelectProductResult('water', '   ', [25 => 1], $snapshot);
            self::fail('The result should have rejected the empty product name.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Dispensed product name cannot be empty.', $exception->getMessage());
        }

        try {
            new SelectProductResult('water', 'Water', [25 => -1], $snapshot);
            self::fail('The result should have rejected negative change counts.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Dispensed change counts cannot be negative.', $exception->getMessage());
        }

        try {
            new SelectProductResult('water', 'Water', ['quarter' => 1], $snapshot);
            self::fail('The result should have rejected invalid denomination keys.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Dispensed change denomination keys must be integer values.', $exception->getMessage());
        }

        try {
            new SelectProductResult('water', 'Water', [25 => '1'], $snapshot);
            self::fail('The result should have rejected non-integer change counts.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Dispensed change counts must be integers.', $exception->getMessage());
        }
    }

    private function snapshot(): MachineSnapshot
    {
        return new MachineSnapshot(
            'default',
            25,
            [25 => 1],
            [5 => 2],
            [
                new ProductSnapshot('water', 65, 2, 'Water'),
                new ProductSnapshot('juice', 100, 0, 'Juice'),
            ],
        );
    }
}
