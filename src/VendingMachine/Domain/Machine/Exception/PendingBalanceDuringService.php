<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when service is attempted while a customer still has money inserted.
 */
final class PendingBalanceDuringService extends DomainException
{
}
