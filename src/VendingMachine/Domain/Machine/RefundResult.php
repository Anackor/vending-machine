<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

final readonly class RefundResult
{
    public function __construct(
        private Machine $machine,
        private InsertedCoins $returnedCoins,
    ) {
    }

    public function machine(): Machine
    {
        return $this->machine;
    }

    public function returnedCoins(): InsertedCoins
    {
        return $this->returnedCoins;
    }
}
