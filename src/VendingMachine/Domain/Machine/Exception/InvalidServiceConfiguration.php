<?php

declare(strict_types=1);

namespace VendingMachine\Domain\Machine\Exception;

use DomainException;

/**
 * Raised when a service operation provides an invalid stock or change configuration.
 */
final class InvalidServiceConfiguration extends DomainException
{
}
