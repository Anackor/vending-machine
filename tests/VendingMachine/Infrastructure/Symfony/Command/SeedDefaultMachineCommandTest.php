<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Infrastructure\Symfony\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\VendingMachine\Application\Machine\Double\InMemoryMachineRepository;
use Tests\VendingMachine\Infrastructure\Persistence\MongoDB\Machine\Fixture\DefaultMachineFixture;
use VendingMachine\Domain\Machine\ValueObject\Coin;
use VendingMachine\Domain\Machine\ValueObject\MachineId;
use VendingMachine\Domain\Machine\ValueObject\Selector;
use VendingMachine\Infrastructure\Symfony\Command\SeedDefaultMachineCommand;

final class SeedDefaultMachineCommandTest extends TestCase
{
    public function testItSeedsTheDefaultMachineWhenItDoesNotExist(): void
    {
        $repository = new InMemoryMachineRepository();
        $command = new SeedDefaultMachineCommand($repository);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);
        $seededMachine = $repository->find(MachineId::default());

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Default machine seeded', $tester->getDisplay());
        self::assertNotNull($seededMachine);
        self::assertSame(10, $seededMachine->availableChange()->countFor(Coin::fromCents(100)));
        self::assertSame(10, $seededMachine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame(8, $seededMachine->productStockFor(Selector::fromString('juice'))?->quantity());
        self::assertSame(5, $seededMachine->productStockFor(Selector::fromString('soda'))?->quantity());
    }

    public function testItDoesNotOverwriteAnExistingMachine(): void
    {
        $existingMachine = DefaultMachineFixture::machine([
            'water' => 3,
            'juice' => 4,
            'soda' => 2,
        ]);
        $repository = new InMemoryMachineRepository(['default' => $existingMachine]);
        $command = new SeedDefaultMachineCommand($repository);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);
        $persistedMachine = $repository->find(MachineId::default());

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('already exists', $tester->getDisplay());
        self::assertNotNull($persistedMachine);
        self::assertSame(3, $persistedMachine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame(4, $persistedMachine->productStockFor(Selector::fromString('juice'))?->quantity());
        self::assertSame(2, $persistedMachine->productStockFor(Selector::fromString('soda'))?->quantity());
    }

    public function testItResetsAnExistingMachineWhenRequested(): void
    {
        $existingMachine = DefaultMachineFixture::machine(
            stockQuantities: [
                'water' => 3,
                'juice' => 4,
                'soda' => 2,
            ],
            availableChangeCounts: [
                5 => 1,
                10 => 2,
                25 => 3,
                100 => 4,
            ],
            insertedCoinCounts: [
                25 => 1,
            ],
        );
        $repository = new InMemoryMachineRepository(['default' => $existingMachine]);
        $command = new SeedDefaultMachineCommand($repository);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['--reset' => true]);
        $persistedMachine = $repository->find(MachineId::default());

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('reset to the documented reviewer baseline', $tester->getDisplay());
        self::assertNotNull($persistedMachine);
        self::assertSame(20, $persistedMachine->availableChange()->countFor(Coin::fromCents(5)));
        self::assertSame(10, $persistedMachine->availableChange()->countFor(Coin::fromCents(100)));
        self::assertSame(0, $persistedMachine->insertedBalance()->cents());
        self::assertSame(10, $persistedMachine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame(8, $persistedMachine->productStockFor(Selector::fromString('juice'))?->quantity());
        self::assertSame(5, $persistedMachine->productStockFor(Selector::fromString('soda'))?->quantity());
    }
}
