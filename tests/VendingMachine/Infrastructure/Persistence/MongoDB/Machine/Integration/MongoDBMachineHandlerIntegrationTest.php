<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Integration;

use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Handler\GetMachineStateHandler;
use VendingMachine\Application\Machine\Handler\InsertCoinHandler;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;
use VendingMachine\Domain\Machine\ValueObject\MachineId;
use VendingMachine\Domain\Machine\ValueObject\Selector;

final class MongoDBMachineHandlerIntegrationTest extends MongoDBIntegrationTestCase
{
    public function testItPersistsStateChangesThroughTheMongoBackedHandlerFlow(): void
    {
        $this->seedDefaultMachine();

        self::assertInstanceOf(InsertCoinHandler::class, $this->insertCoinHandler);
        self::assertInstanceOf(GetMachineStateHandler::class, $this->getMachineStateHandler);

        $insertResult = $this->insertCoinHandler->handle(new InsertCoinCommand(25));
        $stateResult = $this->getMachineStateHandler->handle(new GetMachineStateQuery());
        $reloadedMachine = $this->machineRepository->find(MachineId::default());

        self::assertSame(25, $insertResult->machineSnapshot()->insertedBalanceCents());
        self::assertSame([25 => 1], $insertResult->machineSnapshot()->insertedCoins());
        self::assertSame(25, $stateResult->machineSnapshot()->insertedBalanceCents());
        self::assertSame(10, $stateResult->machineSnapshot()->productSnapshotFor('water')?->quantity());
        self::assertNotNull($reloadedMachine);
        self::assertSame(25, $reloadedMachine->insertedBalance()->cents());
        self::assertSame(10, $reloadedMachine->productStockFor(Selector::fromString('water'))?->quantity());
    }
}
