<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Integration;

use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Domain\Machine\Selector;

final class MongoDBMachineRepositoryTest extends MongoDBIntegrationTestCase
{
    public function testItReturnsNullWhenTheMachineDocumentDoesNotExist(): void
    {
        self::assertNull($this->machineRepository->find(MachineId::default()));
    }

    public function testItSavesAndReloadsTheDefaultMachineAggregate(): void
    {
        $this->machineRepository->save(
            MachineId::default(),
            DefaultMachineFixture::machine(
                ['water' => 2, 'juice' => 3, 'soda' => 4],
                [25 => 2, 5 => 1],
                [100 => 1],
            ),
        );

        $reloadedMachine = $this->machineRepository->find(MachineId::default());
        $persistedDocument = $this->machineCollection()->findOne(
            ['_id' => 'default'],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']],
        );

        self::assertNotNull($reloadedMachine);
        self::assertIsArray($persistedDocument);
        self::assertSame(100, $reloadedMachine->insertedBalance()->cents());
        self::assertSame([5 => 1, 25 => 2], $reloadedMachine->availableChange()->counts());
        self::assertSame([100 => 1], $reloadedMachine->insertedCoins()->counts());
        self::assertSame(2, $reloadedMachine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame('default', $persistedDocument['_id']);
    }

    public function testItOverwritesAnExistingMachineDocumentInsteadOfDuplicatingIt(): void
    {
        $this->seedDefaultMachine(
            DefaultMachineFixture::machine(
                ['water' => 1, 'juice' => 2, 'soda' => 3],
                [25 => 1],
                [10 => 1],
            ),
        );

        $this->machineRepository->save(
            MachineId::default(),
            DefaultMachineFixture::machine(
                ['water' => 7, 'juice' => 6, 'soda' => 5],
                [100 => 1],
                [25 => 2],
            ),
        );

        $reloadedMachine = $this->machineRepository->find(MachineId::default());
        $documents = $this->machineCollection()->find(
            [],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']],
        )->toArray();

        self::assertNotNull($reloadedMachine);
        self::assertCount(1, $documents);
        self::assertSame(7, $reloadedMachine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame([100 => 1], $reloadedMachine->availableChange()->counts());
        self::assertSame([25 => 2], $reloadedMachine->insertedCoins()->counts());
    }

    public function testItReconstructsAValidMachineFromPersistedData(): void
    {
        $this->insertMachineDocument(
            DefaultMachineFixture::document(
                ['water' => 2, 'juice' => 1, 'soda' => 3],
                [25 => 2, 10 => 1],
                [100 => 1],
            ),
        );

        $machine = $this->machineRepository->find(MachineId::default());

        self::assertNotNull($machine);
        self::assertTrue($machine->canPurchase(Selector::fromString('water')));
        self::assertSame(100, $machine->insertedBalance()->cents());
        self::assertSame(1, $machine->productStockFor(Selector::fromString('juice'))?->quantity());
    }
}
