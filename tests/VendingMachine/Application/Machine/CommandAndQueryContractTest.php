<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Application\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;
use VendingMachine\Domain\Machine\AvailableChange;
use VendingMachine\Domain\Machine\MachineId;
use VendingMachine\Domain\Machine\Selector;
use VendingMachine\Domain\Machine\StockQuantity;

final class CommandAndQueryContractTest extends TestCase
{
    public function testItBuildsAnInsertCoinCommandWithTheDefaultMachineId(): void
    {
        $command = new InsertCoinCommand(25);

        self::assertSame(25, $command->coinCents());
        self::assertTrue(MachineId::default()->equals($command->machineId()));
    }

    public function testItNormalizesMachineIdAndSelectorInputs(): void
    {
        $selectProduct = new SelectProductCommand(' WATER ', ' DEFAULT ');
        $returnInsertedMoney = new ReturnInsertedMoneyCommand(' DEFAULT ');
        $getMachineState = new GetMachineStateQuery(' DEFAULT ');

        self::assertSame('water', $selectProduct->selector()->value());
        self::assertSame('default', $selectProduct->machineId()->value());
        self::assertSame('default', $returnInsertedMoney->machineId()->value());
        self::assertSame('default', $getMachineState->machineId()->value());
    }

    public function testItBuildsAServiceMachineCommandWithNormalizedCounts(): void
    {
        $command = new ServiceMachineCommand(
            [
                ' WATER ' => 2,
                'Juice' => 3,
            ],
            [
                '25' => 2,
                5 => 1,
            ],
            ' DEFAULT ',
        );

        self::assertSame('default', $command->machineId()->value());
        self::assertSame(['juice', 'water'], array_keys($command->productQuantities()));
        self::assertSame(3, $command->productQuantities()['juice']->value());
        self::assertSame(2, $command->productQuantities()['water']->value());
        self::assertSame([5 => 1, 25 => 2], $command->availableChangeCounts());
    }

    public function testItBuildsTheContractsWithAnExplicitNormalizedMachineId(): void
    {
        $insertCoin = new InsertCoinCommand(25, ' DEFAULT ');
        $returnInsertedMoney = new ReturnInsertedMoneyCommand(' DEFAULT ');
        $getMachineState = new GetMachineStateQuery(' DEFAULT ');

        self::assertSame('default', $insertCoin->machineId()->value());
        self::assertSame('default', $returnInsertedMoney->machineId()->value());
        self::assertSame('default', $getMachineState->machineId()->value());
    }

    public function testItAcceptsAMachineIdValueObject(): void
    {
        $machineId = MachineId::fromString(' Lobby-01 ');
        $command = new InsertCoinCommand(25, $machineId);

        self::assertSame($machineId, $command->machineId());
        self::assertSame('lobby-01', $command->machineId()->value());
    }

    public function testItAcceptsASelectorValueObject(): void
    {
        $selector = Selector::fromString(' WATER ');
        $command = new SelectProductCommand($selector);

        self::assertSame($selector, $command->selector());
        self::assertSame('water', $command->selector()->value());
    }

    public function testItAcceptsStockQuantityValueObjects(): void
    {
        $quantity = StockQuantity::fromInt(4);
        $command = new ServiceMachineCommand(
            ['water' => $quantity],
            [25 => 1],
        );

        self::assertSame($quantity, $command->productQuantities()['water']);
        self::assertSame(4, $command->productQuantities()['water']->value());
    }

    public function testItAcceptsAvailableChangeValueObjects(): void
    {
        $availableChange = AvailableChange::fromCounts(['25' => 2, 5 => 1]);
        $command = new ServiceMachineCommand(
            ['water' => 2],
            $availableChange,
        );

        self::assertSame($availableChange, $command->availableChange());
        self::assertSame([5 => 1, 25 => 2], $command->availableChangeCounts());
    }

    public function testItRejectsNonPositiveInsertCoinAmounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Insert coin amount must be greater than zero.');

        new InsertCoinCommand(0);
    }

    public function testItRejectsEmptyMachineIdsForInsertCoinCommands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new InsertCoinCommand(25, '   ');
    }

    public function testItRejectsEmptyMachineIdsForCommandsAndQueries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new ReturnInsertedMoneyCommand('   ');
    }

    public function testItRejectsEmptyProductSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector cannot be empty.');

        new SelectProductCommand('   ');
    }

    public function testItRejectsInvalidProductSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector "water!" is invalid.');

        new SelectProductCommand('water!');
    }

    public function testItRejectsEmptyMachineIdsForSelectProductCommands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new SelectProductCommand('water', '   ');
    }

    public function testItRejectsEmptyMachineIds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new GetMachineStateQuery('   ');
    }

    public function testItRejectsEmptyServiceProductQuantities(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service product quantities cannot be empty.');

        new ServiceMachineCommand([], [25 => 1]);
    }

    public function testItRejectsInvalidServiceProductQuantityShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service product quantities must be integers.');

        new ServiceMachineCommand(
            ['water' => '2'],
            [25 => 1],
        );
    }

    public function testItRejectsNegativeServiceProductQuantities(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock quantity cannot be negative.');

        new ServiceMachineCommand(
            ['water' => -1],
            [25 => 1],
        );
    }

    public function testItRejectsEmptyServiceProductSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector cannot be empty.');

        new ServiceMachineCommand(
            ['   ' => 1],
            [25 => 1],
        );
    }

    public function testItRejectsInvalidServiceProductSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Selector "water!" is invalid.');

        new ServiceMachineCommand(
            ['water!' => 1],
            [25 => 1],
        );
    }

    public function testItRejectsNonStringServiceProductSelectors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service product selectors must be strings.');

        new ServiceMachineCommand(
            [1 => 1],
            [25 => 1],
        );
    }

    public function testItRejectsInvalidServiceChangeShapes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin denomination keys must be integer values.');

        new ServiceMachineCommand(
            ['water' => 2],
            ['quarter' => 1],
        );
    }

    public function testItRejectsUnsupportedServiceChangeDenominations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported coin denomination "50".');

        new ServiceMachineCommand(
            ['water' => 2],
            [50 => 1],
        );
    }

    public function testItRejectsNonIntegerServiceChangeCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin counts must be integers.');

        new ServiceMachineCommand(
            ['water' => 2],
            [25 => '1'],
        );
    }

    public function testItRejectsNegativeServiceChangeCounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coin counts cannot be negative.');

        new ServiceMachineCommand(
            ['water' => 2],
            [25 => -1],
        );
    }

    public function testItRejectsEmptyMachineIdsForServiceCommands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Machine id cannot be empty.');

        new ServiceMachineCommand(
            ['water' => 2],
            [25 => 1],
            '   ',
        );
    }
}
