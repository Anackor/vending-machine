<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Machine;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\Machine\Exception\ExactChangeNotAvailable;
use VendingMachine\Domain\Machine\Exception\InsufficientBalance;
use VendingMachine\Domain\Machine\Exception\InvalidServiceConfiguration;
use VendingMachine\Domain\Machine\Exception\PendingBalanceDuringService;
use VendingMachine\Domain\Machine\Exception\ProductNotFound;
use VendingMachine\Domain\Machine\Exception\ProductOutOfStock;
use VendingMachine\Domain\Machine\Machine;
use VendingMachine\Domain\Machine\Product;
use VendingMachine\Domain\Machine\ProductStock;
use VendingMachine\Domain\Machine\ValueObject\AvailableChange;
use VendingMachine\Domain\Machine\ValueObject\InsertedCoins;
use VendingMachine\Domain\Machine\ValueObject\Money;
use VendingMachine\Domain\Machine\ValueObject\Selector;

final class MachineBehaviorTest extends TestCase
{
    public function testItInsertsCoinsAndExposesPurchaseAvailability(): void
    {
        $machine = $this->machine()
            ->insertCoinValue(25)
            ->insertCoinValue(25)
            ->insertCoinValue(10)
            ->insertCoinValue(5);

        self::assertSame(65, $machine->insertedBalance()->cents());
        self::assertTrue($machine->canPurchase(Selector::fromString('water')));
        self::assertFalse($machine->canPurchase(Selector::fromString('soda')));
    }

    public function testItRejectsUnsupportedCoinInsertionAttempts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported coin denomination "50".');

        $this->machine()->insertCoinValue(50);
    }

    public function testItReportsWhenAProductCannotBePurchasedBecauseExactChangeIsUnavailable(): void
    {
        $machine = $this->machine()->insertCoinValue(100);

        self::assertFalse($machine->canPurchase(Selector::fromString('water')));
        self::assertSame(100, $machine->insertedBalance()->cents());
        self::assertSame([], $machine->availableChange()->counts());
    }

    public function testItPurchasesWithTheExactAmount(): void
    {
        $machine = $this->machine()
            ->insertCoinValue(25)
            ->insertCoinValue(25)
            ->insertCoinValue(10)
            ->insertCoinValue(5);

        $purchase = $machine->purchase(Selector::fromString('water'));

        self::assertSame('Water', $purchase->product()->name());
        self::assertSame([], $purchase->change()->counts());
        self::assertTrue($purchase->machine()->insertedCoins()->isEmpty());
        self::assertSame(9, $purchase->machine()->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame([5 => 1, 10 => 1, 25 => 2], $purchase->machine()->availableChange()->counts());
    }

    public function testItPurchasesAndReturnsChangeUsingCommittedInsertedCoins(): void
    {
        $machine = $this->machine()
            ->insertCoinValue(100)
            ->insertCoinValue(25);

        $purchase = $machine->purchase(Selector::fromString('juice'));

        self::assertSame('Juice', $purchase->product()->name());
        self::assertSame([25 => 1], $purchase->change()->counts());
        self::assertSame([100 => 1], $purchase->machine()->availableChange()->counts());
        self::assertSame(7, $purchase->machine()->productStockFor(Selector::fromString('juice'))?->quantity());
    }

    public function testItRejectsUnknownSelectorsWithoutMutatingState(): void
    {
        $machine = $this->machine()->insertCoinValue(25);

        try {
            $machine->purchase(Selector::fromString('chips'));
            self::fail('The purchase should have failed for an unknown selector.');
        } catch (ProductNotFound $exception) {
            self::assertSame('Unknown selector "chips".', $exception->getMessage());
            self::assertSame(25, $machine->insertedBalance()->cents());
            self::assertSame(10, $machine->productStockFor(Selector::fromString('water'))?->quantity());
        }
    }

    public function testItRejectsOutOfStockProductsWithoutMutatingState(): void
    {
        $machine = Machine::initialize(
            [
                $this->productStock('water', 'Water', 65, 0),
                $this->productStock('juice', 'Juice', 100, 8),
                $this->productStock('soda', 'Soda', 150, 5),
            ],
            AvailableChange::empty(),
            InsertedCoins::fromCounts([100 => 1]),
        );

        try {
            $machine->purchase(Selector::fromString('water'));
            self::fail('The purchase should have failed for an out-of-stock product.');
        } catch (ProductOutOfStock $exception) {
            self::assertSame('Product "Water" is out of stock.', $exception->getMessage());
            self::assertSame(100, $machine->insertedBalance()->cents());
            self::assertSame(0, $machine->productStockFor(Selector::fromString('water'))?->quantity());
        }
    }

    public function testItRejectsPurchasesWithInsufficientBalanceWithoutMutatingState(): void
    {
        $machine = $this->machine()->insertCoinValue(25);

        try {
            $machine->purchase(Selector::fromString('water'));
            self::fail('The purchase should have failed for insufficient balance.');
        } catch (InsufficientBalance $exception) {
            self::assertSame('Inserted balance "25" is insufficient for product price "65".', $exception->getMessage());
            self::assertSame(25, $machine->insertedBalance()->cents());
            self::assertSame(10, $machine->productStockFor(Selector::fromString('water'))?->quantity());
        }
    }

    public function testItRejectsPurchasesWhenExactChangeCannotBeReturned(): void
    {
        $machine = $this->machine()->insertCoinValue(100);

        try {
            $machine->purchase(Selector::fromString('water'));
            self::fail('The purchase should have failed because exact change is not available.');
        } catch (ExactChangeNotAvailable $exception) {
            self::assertSame('Exact change "35" cannot be returned for selector "water".', $exception->getMessage());
            self::assertSame(100, $machine->insertedBalance()->cents());
            self::assertSame(10, $machine->productStockFor(Selector::fromString('water'))?->quantity());
            self::assertSame([], $machine->availableChange()->counts());
        }
    }

    public function testItRefundsTheExactInsertedCoinsAndClearsTheBalance(): void
    {
        $machine = $this->machine()
            ->insertCoinValue(100)
            ->insertCoinValue(25);

        $refund = $machine->refund();

        self::assertSame([25 => 1, 100 => 1], $refund->returnedCoins()->counts());
        self::assertTrue($refund->machine()->insertedCoins()->isEmpty());
        self::assertSame([], $refund->machine()->availableChange()->counts());
    }

    public function testItRefundsWithoutMutatingTheOriginalMachine(): void
    {
        $machine = $this->machine()
            ->insertCoinValue(100)
            ->insertCoinValue(25);

        $refund = $machine->refund();

        self::assertSame([25 => 1, 100 => 1], $machine->insertedCoins()->counts());
        self::assertSame(125, $machine->insertedBalance()->cents());
        self::assertTrue($refund->machine()->insertedCoins()->isEmpty());
    }

    public function testItServicesStockAndAvailableChangeWhenNoBalanceIsPending(): void
    {
        $machine = $this->machine()->service(
            [
                'water' => 2,
                'juice' => 3,
                'soda' => 4,
            ],
            [
                5 => 1,
                25 => 2,
            ],
        );

        self::assertSame(2, $machine->productStockFor(Selector::fromString('water'))?->quantity());
        self::assertSame(3, $machine->productStockFor(Selector::fromString('juice'))?->quantity());
        self::assertSame(4, $machine->productStockFor(Selector::fromString('soda'))?->quantity());
        self::assertSame([5 => 1, 25 => 2], $machine->availableChange()->counts());
    }

    public function testItRejectsServiceWhenACustomerBalanceIsPending(): void
    {
        $machine = $this->machine()->insertCoinValue(25);

        $this->expectException(PendingBalanceDuringService::class);
        $this->expectExceptionMessage('Machine service requires no pending customer balance.');

        $machine->service(
            [
                'water' => 2,
                'juice' => 3,
                'soda' => 4,
            ],
            [25 => 1],
        );
    }

    public function testItRejectsServiceWhenAStockCountIsMissing(): void
    {
        $this->expectException(InvalidServiceConfiguration::class);
        $this->expectExceptionMessage('Missing stock count for selector "juice".');

        $this->machine()->service(
            [
                'water' => 2,
                'soda' => 4,
            ],
            [25 => 1],
        );
    }

    public function testItRejectsServiceWhenAStockCountIsNotAnInteger(): void
    {
        $this->expectException(InvalidServiceConfiguration::class);
        $this->expectExceptionMessage('Stock count for selector "water" must be an integer.');

        $this->machine()->service(
            [
                'water' => '2',
                'juice' => 3,
                'soda' => 4,
            ],
            [25 => 1],
        );
    }

    public function testItRejectsServiceWhenAnUnknownSelectorIsProvided(): void
    {
        $this->expectException(InvalidServiceConfiguration::class);
        $this->expectExceptionMessage('Unknown selector "chips" in service stock configuration.');

        $this->machine()->service(
            [
                'water' => 2,
                'juice' => 3,
                'soda' => 4,
                'chips' => 1,
            ],
            [25 => 1],
        );
    }

    private function machine(): Machine
    {
        return Machine::initialize([
            $this->productStock('water', 'Water', 65, 10),
            $this->productStock('juice', 'Juice', 100, 8),
            $this->productStock('soda', 'Soda', 150, 5),
        ]);
    }

    private function productStock(string $selector, string $name, int $price, int $quantity): ProductStock
    {
        return new ProductStock(
            new Product(
                Selector::fromString($selector),
                Money::fromCents($price),
                $name,
            ),
            $quantity,
        );
    }
}
