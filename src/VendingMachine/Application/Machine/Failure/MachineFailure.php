<?php

declare(strict_types=1);

namespace VendingMachine\Application\Machine\Failure;

use InvalidArgumentException;

/**
 * Stable application-level failure payload exposed to HTTP and CLI adapters.
 */
final readonly class MachineFailure
{
    /**
     * @var array<string, bool|float|int|string>
     */
    private array $context;

    /**
     * @param array<string, bool|float|int|string> $context
     */
    public function __construct(
        private MachineFailureCode $code,
        string $message,
        array $context = [],
    ) {
        $this->message = trim($message);

        if ($this->message === '') {
            throw new InvalidArgumentException('Application failure message cannot be empty.');
        }

        ksort($context);
        $this->context = $context;
    }

    private string $message;

    public function code(): MachineFailureCode
    {
        return $this->code;
    }

    /**
     * @return array<string, bool|float|int|string>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function message(): string
    {
        return $this->message;
    }
}
