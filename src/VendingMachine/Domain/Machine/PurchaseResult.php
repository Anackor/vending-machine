<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

/**
 * Returns the updated machine, the dispensed product, and the exact change of a purchase.
 */
final readonly class PurchaseResult
{
    public function __construct(
        private Machine $machine,
        private Product $product,
        private CoinInventory $change,
    ) {
    }

    public function machine(): Machine
    {
        return $this->machine;
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function change(): CoinInventory
    {
        return $this->change;
    }
}
