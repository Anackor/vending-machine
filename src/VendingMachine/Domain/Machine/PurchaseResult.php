<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

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
