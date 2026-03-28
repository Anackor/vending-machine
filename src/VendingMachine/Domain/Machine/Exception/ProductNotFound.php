<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when a selector does not match any product known by the machine.
 */
final class ProductNotFound extends DomainException
{
}
