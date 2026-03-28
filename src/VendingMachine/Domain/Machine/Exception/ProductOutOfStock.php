<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when the selected product exists but its current stock is empty.
 */
final class ProductOutOfStock extends DomainException
{
}
