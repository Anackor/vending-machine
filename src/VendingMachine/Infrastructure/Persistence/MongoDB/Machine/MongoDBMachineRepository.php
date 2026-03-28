<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence\MongoDB\Machine;

use MongoDB\Collection;
use MongoDB\Database;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Mapper\MachineDocumentMapper;

/**
 * MongoDB adapter that implements the application repository port for one machine aggregate.
 */
final readonly class MongoDBMachineRepository implements MachineRepository
{
    public const string COLLECTION_NAME = 'machines';

    /**
     * @var array<string, array<string, string>>
     */
    private const array TYPE_MAP = [
        'typeMap' => [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ],
    ];

    private Collection $collection;

    public function __construct(
        Database $database,
        private MachineDocumentMapper $machineDocumentMapper,
    ) {
        $this->collection = $database->selectCollection(self::COLLECTION_NAME);
    }

    public function find(string $machineId): ?Machine
    {
        // MongoDB returns arrays here so the mapper can validate the persisted shape explicitly.
        $document = $this->collection->findOne(['_id' => $machineId], self::TYPE_MAP);

        if ($document === null) {
            return null;
        }

        /** @var array<string, mixed> $document */
        return $this->machineDocumentMapper->toDomain(
            $this->machineDocumentMapper->fromPersistence($document),
        );
    }

    public function save(string $machineId, Machine $machine): void
    {
        // The aggregate is persisted as one logical replacement to keep the write model simple.
        $document = $this->machineDocumentMapper->fromDomain($machineId, $machine);

        $this->collection->replaceOne(
            ['_id' => $document->machineId()],
            $this->machineDocumentMapper->toPersistence($document),
            ['upsert' => true],
        );
    }
}
