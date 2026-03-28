<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when the inserted customer balance does not cover the selected product price.
 */
final class InsufficientBalance extends DomainException
{
}
