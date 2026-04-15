<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Integration;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Application\Machine\Factory\MachineFailureFactory;
use VendingMachine\Application\Machine\Factory\MachineSnapshotFactory;
use VendingMachine\Application\Machine\Handler\GetMachineStateHandler;
use VendingMachine\Application\Machine\Handler\InsertCoinHandler;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper\MachineDocumentMapper;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\MongoDBMachineRepository;

abstract class MongoDBIntegrationTestCase extends TestCase
{
    protected Database $database;
    protected MachineRepository $machineRepository;
    protected InsertCoinHandler $insertCoinHandler;
    protected GetMachineStateHandler $getMachineStateHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $client = new Client($this->environmentVariable('MONGODB_URI'));
        $this->database = $client->selectDatabase($this->environmentVariable('MONGODB_DATABASE'));

        $machineDocumentMapper = new MachineDocumentMapper();
        $machineSnapshotFactory = new MachineSnapshotFactory();
        $machineFailureFactory = new MachineFailureFactory();

        $this->machineRepository = new MongoDBMachineRepository(
            $this->database,
            $machineDocumentMapper,
        );
        $this->insertCoinHandler = new InsertCoinHandler(
            $this->machineRepository,
            $machineSnapshotFactory,
            $machineFailureFactory,
        );
        $this->getMachineStateHandler = new GetMachineStateHandler(
            $this->machineRepository,
            $machineSnapshotFactory,
            $machineFailureFactory,
        );

        $this->clearMachineCollection();
    }

    protected function tearDown(): void
    {
        $this->clearMachineCollection();

        parent::tearDown();
    }

    protected function clearMachineCollection(): void
    {
        $this->machineCollection()->deleteMany([]);
    }

    /**
     * @param array<string, mixed> $document
     */
    protected function insertMachineDocument(array $document): void
    {
        $this->machineCollection()->insertOne($document);
    }

    protected function seedDefaultMachine(?Machine $machine = null): void
    {
        $this->machineRepository->save(MachineId::default(), $machine ?? DefaultMachineFixture::machine());
    }

    protected function machineCollection(): Collection
    {
        return $this->database->selectCollection(MongoDBMachineRepository::COLLECTION_NAME);
    }

    private function environmentVariable(string $name): string
    {
        $value = $_SERVER[$name] ?? getenv($name);

        if (!is_string($value) || $value === '') {
            throw new \RuntimeException(sprintf('Missing required environment variable "%s" for MongoDB integration tests.', $name));
        }

        return $value;
    }
}
