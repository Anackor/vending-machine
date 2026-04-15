<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\InsertedCoins;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\MachineDocument;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\ProductStockDocument;

final class MachineDocumentTest extends TestCase
{
    public function testItBuildsAMachineDocumentWithNormalizedCoinCounts(): void
    {
        $document = new MachineDocument(
            'default',
            [
                new ProductStockDocument('water', 65, 2, 'Water'),
                new ProductStockDocument('juice', 100, 0, 'Juice'),
            ],
            [25 => 2, 5 => 1, 10 => 0],
            ['100' => 1],
        );

        self::assertSame('default', $document->machineId());
        self::assertCount(2, $document->productStocks());
        self::assertSame([5 => 1, 25 => 2], $document->availableChangeCounts());
        self::assertSame([100 => 1], $document->insertedCoinCounts());
    }

    public function testItAcceptsCoinStateValueObjects(): void
    {
        $availableChange = AvailableChange::fromCounts([25 => 2, 5 => 1]);
        $insertedCoins = InsertedCoins::fromCounts([100 => 1]);
        $document = new MachineDocument(
            'default',
            [new ProductStockDocument('water', 65, 2, 'Water')],
            $availableChange,
            $insertedCoins,
        );

        self::assertSame($availableChange, $document->availableChange());
        self::assertSame($insertedCoins, $document->insertedCoins());
        self::assertSame([5 => 1, 25 => 2], $document->availableChangeCounts());
        self::assertSame([100 => 1], $document->insertedCoinCounts());
    }

    public function testItRejectsInvalidMachineDocumentShapes(): void
    {
        try {
            new MachineDocument('   ', [new ProductStockDocument('water', 65, 1, 'Water')], [], []);
            self::fail('The document should reject empty machine ids.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine id cannot be empty.', $exception->getMessage());
        }

        try {
            new MachineDocument('default', [], [], []);
            self::fail('The document should reject empty product lists.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine document must contain product stocks.', $exception->getMessage());
        }

        try {
            new MachineDocument(
                'default',
                [
                    new ProductStockDocument('water', 65, 1, 'Water'),
                    new ProductStockDocument('water', 65, 2, 'Still Water'),
                ],
                [],
                [],
            );
            self::fail('The document should reject duplicate selectors.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Duplicate persisted product selector "water" detected.', $exception->getMessage());
        }

        try {
            new MachineDocument(
                'default',
                [new ProductStockDocument('water', 65, 1, 'Water')],
                ['quarter' => 1],
                [],
            );
            self::fail('The document should reject invalid coin denomination keys.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Coin denomination keys must be integer values.', $exception->getMessage());
        }

        try {
            new MachineDocument(
                'default',
                [new ProductStockDocument('water', 65, 1, 'Water')],
                [],
                [25 => '1'],
            );
            self::fail('The document should reject non-integer coin counts.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Coin counts must be integers.', $exception->getMessage());
        }

        try {
            new MachineDocument(
                'default',
                [new ProductStockDocument('water', 65, 1, 'Water')],
                [25 => -1],
                [],
            );
            self::fail('The document should reject negative available change counts.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Coin counts cannot be negative.', $exception->getMessage());
        }

        try {
            new MachineDocument(
                'default',
                [new ProductStockDocument('water', 65, 1, 'Water')],
                [],
                ['quarter' => 1],
            );
            self::fail('The document should reject invalid inserted coin denomination keys.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Coin denomination keys must be integer values.', $exception->getMessage());
        }
    }
}
