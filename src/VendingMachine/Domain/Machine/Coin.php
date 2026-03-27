<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine;

use InvalidArgumentException;

enum Coin: int
{
    case FiveCents = 5;
    case TenCents = 10;
    case TwentyFiveCents = 25;
    case OneHundredCents = 100;

    public static function fromCents(int $cents): self
    {
        return self::tryFrom($cents)
            ?? throw new InvalidArgumentException(sprintf('Unsupported coin denomination "%d".', $cents));
    }

    public function money(): Money
    {
        return Money::fromCents($this->value);
    }
}
