<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Command;

use DateTimeImmutable;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:mongodb:smoke',
    description: 'Runs a MongoDB connectivity and round-trip smoke check.',
)]
final class MongoDBSmokeCommand extends Command
{
    public function __construct(
        private readonly Database $database,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $collection = $this->database->selectCollection('phase0_smoke_checks');
        $documentId = new ObjectId();

        try {
            $this->insertSmokeDocument($collection, $documentId);

            $document = $collection->findOne(['_id' => $documentId]);

            if ($document === null) {
                $io->error('MongoDB smoke check failed: inserted document could not be read back.');

                return Command::FAILURE;
            }

            $collection->deleteOne(['_id' => $documentId]);
        } catch (Throwable $exception) {
            $io->error(sprintf('MongoDB smoke check failed: %s', $exception->getMessage()));

            return Command::FAILURE;
        }

        $io->success('MongoDB smoke check passed: connectivity and round-trip are working.');

        return Command::SUCCESS;
    }

    private function insertSmokeDocument(Collection $collection, ObjectId $documentId): void
    {
        $collection->insertOne([
            '_id' => $documentId,
            'scope' => 'phase_0_foundation',
            'createdAt' => new DateTimeImmutable()->format(DATE_ATOM),
        ]);
    }
}
