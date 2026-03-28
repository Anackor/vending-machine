<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when the machine cannot return the exact change for a valid purchase.
 */
final class ExactChangeNotAvailable extends DomainException
{
}
