<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use VendingMachine\Application\Machine\Repository\MachineRepository;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\InsertedCoins;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Domain\Machine\Money;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\Selector;

/**
 * Seeds or resets the documented default machine used by reviewers and local setup.
 */
#[AsCommand(
    name: 'app:machine:seed-default',
    description: 'Seeds the default machine for reviewer-facing HTTP flows.',
)]
final class SeedDefaultMachineCommand extends Command
{
    public function __construct(
        private readonly MachineRepository $machineRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'reset',
            null,
            InputOption::VALUE_NONE,
            'Reset the default machine to the documented reviewer baseline even if it already exists.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Bootstrap stays deterministic by reusing one documented baseline for the default machine.
        $io = new SymfonyStyle($input, $output);
        $reset = $input->getOption('reset');

        $machineId = MachineId::default();

        if (!$reset && $this->machineRepository->find($machineId) !== null) {
            $io->success('Default machine already exists. No changes were required.');

            return Command::SUCCESS;
        }

        $this->machineRepository->save($machineId, $this->defaultMachine());
        $io->success(
            $reset
                ? 'Default machine reset to the documented reviewer baseline.'
                : 'Default machine seeded for reviewer-facing HTTP flows.',
        );

        return Command::SUCCESS;
    }

    private function defaultMachine(): Machine
    {
        // These values match the baseline described in the README and reviewer guide.
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
