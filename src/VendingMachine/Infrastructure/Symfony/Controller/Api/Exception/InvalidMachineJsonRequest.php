<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Exception;

use RuntimeException;

/**
 * Signals that the HTTP JSON contract was rejected before reaching Application.
 */
final class InvalidMachineJsonRequest extends RuntimeException
{
}
