<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;

#[AsCommand(
    name: 'app:machine:seed-default',
    description: 'Seeds the default machine for reviewer-facing HTTP flows when it does not exist.',
)]
final class SeedDefaultMachineCommand extends Command
{
    public function __construct(
        private readonly MachineRepository $machineRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->machineRepository->find('default') !== null) {
            $io->success('Default machine already exists. No changes were required.');

            return Command::SUCCESS;
        }

        $this->machineRepository->save('default', $this->defaultMachine());
        $io->success('Default machine seeded for reviewer-facing HTTP flows.');

        return Command::SUCCESS;
    }

    private function defaultMachine(): Machine
    {
        return Machine::initialize(
            [
                $this->productStock('water', 'Water', 65, 10),
                $this->productStock('juice', 'Juice', 100, 8),
                $this->productStock('soda', 'Soda', 150, 5),
            ],
            AvailableChange::fromCounts([
                5 => 20,
                10 => 20,
                25 => 20,
                100 => 10,
            ]),
            InsertedCoins::fromCounts([]),
        );
    }

    private function productStock(
        string $selector,
        string $name,
        int $priceCents,
        int $quantity,
    ): ProductStock {
        return new ProductStock(
            new Product(
                Selector::fromString($selector),
                Money::fromCents($priceCents),
                $name,
            ),
            $quantity,
        );
    }
}
