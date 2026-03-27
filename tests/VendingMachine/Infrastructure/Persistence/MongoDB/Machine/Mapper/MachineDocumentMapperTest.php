<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Document\MachineDocument;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper\MachineDocumentMapper;

final class MachineDocumentMapperTest extends TestCase
{
    public function testItMapsTheMachineAggregateIntoADocument(): void
    {
        $mapper = new MachineDocumentMapper();
        $document = $mapper->fromDomain(
            'default',
            DefaultMachineFixture::machine(
                ['water' => 2, 'juice' => 3, 'soda' => 4],
                [25 => 2, 5 => 1],
                [100 => 1],
            ),
        );

        self::assertSame('default', $document->machineId());
        self::assertSame([5 => 1, 25 => 2], $document->availableChangeCounts());
        self::assertSame([100 => 1], $document->insertedCoinCounts());
        self::assertCount(3, $document->productStocks());
        self::assertSame('water', $document->productStocks()[0]->selector());
        self::assertSame('Water', $document->productStocks()[0]->name());
        self::assertSame(65, $document->productStocks()[0]->priceCents());
        self::assertSame(2, $document->productStocks()[0]->quantity());
    }

    public function testItConvertsADocumentIntoTheMongoPersistenceShape(): void
    {
        $mapper = new MachineDocumentMapper();
        $document = new MachineDocument(
            'default',
            $mapper->fromDomain(
                'default',
                DefaultMachineFixture::machine(
                    ['water' => 2, 'juice' => 3, 'soda' => 4],
                    [25 => 2, 5 => 1],
                    [100 => 1],
                ),
            )->productStocks(),
            [25 => 2, 5 => 1],
            [100 => 1],
        );

        self::assertSame(
            DefaultMachineFixture::document(
                ['water' => 2, 'juice' => 3, 'soda' => 4],
                [25 => 2, 5 => 1],
                [100 => 1],
            ),
            $mapper->toPersistence($document),
        );
    }

    public function testItMapsPersistedDocumentsBackIntoTheMachineAggregate(): void
    {
        $mapper = new MachineDocumentMapper();
        $machine = $mapper->toDomain(
            $mapper->fromPersistence(
                DefaultMachineFixture::document(
                    ['water' => 2, 'juice' => 3, 'soda' => 4],
                    [25 => 2, 5 => 1],
                    [100 => 1],
                ),
            ),
        );

        self::assertSame(100, $machine->insertedBalance()->cents());
        self::assertSame([5 => 1, 25 => 2], $machine->availableChange()->counts());
        self::assertSame([100 => 1], $machine->insertedCoins()->counts());
        self::assertSame(2, $machine->productStockFor(\VendingMachine\Domain\Machine\Selector::fromString('water'))?->quantity());
    }

    public function testItRejectsInvalidPersistedShapes(): void
    {
        $mapper = new MachineDocumentMapper();

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => 'invalid',
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-list products.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine document field "products" must be a list.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 10,
                'products' => [],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-string ids.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine document id must be a string.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    [
                        'selector' => 'water',
                        'name' => 'Water',
                        'priceCents' => '65',
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-integer product prices.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted product price cents must be an integer.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    [
                        'selector' => 'water',
                        'name' => 'Water',
                        'priceCents' => 65,
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => 'invalid',
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-array available change fields.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame(
                'Persisted machine document field "availableChange" must be an array.',
                $exception->getMessage(),
            );
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => ['invalid'],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-array product documents.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine products must be document arrays.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    'water' => [
                        'selector' => 'water',
                        'name' => 'Water',
                        'priceCents' => 65,
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject associative product collections.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted machine document products must be a list.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    [
                        'selector' => 10,
                        'name' => 'Water',
                        'priceCents' => 65,
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-string selectors.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted product selector must be a string.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    [
                        'selector' => 'water',
                        'name' => 10,
                        'priceCents' => 65,
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => [],
                'insertedCoins' => [],
            ]);
            self::fail('The mapper should reject non-string names.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame('Persisted product name must be a string.', $exception->getMessage());
        }

        try {
            $mapper->fromPersistence([
                '_id' => 'default',
                'products' => [
                    [
                        'selector' => 'water',
                        'name' => 'Water',
                        'priceCents' => 65,
                        'quantity' => 1,
                    ],
                ],
                'availableChange' => [],
                'insertedCoins' => 'invalid',
            ]);
            self::fail('The mapper should reject non-array inserted coin fields.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame(
                'Persisted machine document field "insertedCoins" must be an array.',
                $exception->getMessage(),
            );
        }
    }
}
