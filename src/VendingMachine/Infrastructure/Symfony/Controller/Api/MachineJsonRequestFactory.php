<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Application\Machine\Command\InsertCoinCommand;
use VendingMachine\Application\Machine\Command\ReturnInsertedMoneyCommand;
use VendingMachine\Application\Machine\Command\SelectProductCommand;
use VendingMachine\Application\Machine\Command\ServiceMachineCommand;
use VendingMachine\Application\Machine\Query\GetMachineStateQuery;

final class MachineJsonRequestFactory
{
    public function createGetMachineStateQuery(): GetMachineStateQuery
    {
        return new GetMachineStateQuery();
    }

    public function createInsertCoinCommand(Request $request): InsertCoinCommand
    {
        $payload = $this->payload($request);

        return new InsertCoinCommand(
            $this->requiredInt($payload, 'coinCents'),
        );
    }

    public function createReturnInsertedMoneyCommand(): ReturnInsertedMoneyCommand
    {
        return new ReturnInsertedMoneyCommand();
    }

    public function createServiceMachineCommand(Request $request): ServiceMachineCommand
    {
        $payload = $this->payload($request);

        return new ServiceMachineCommand(
            $this->requiredObject($payload, 'productQuantities'),
            $this->requiredObject($payload, 'availableChangeCounts'),
        );
    }

    public function createSelectProductCommand(Request $request): SelectProductCommand
    {
        $payload = $this->payload($request);

        return new SelectProductCommand(
            $this->requiredString($payload, 'selector'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $content = trim($request->getContent());

        if ($content === '') {
            return [];
        }

        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($payload) || ($payload !== [] && array_is_list($payload))) {
            throw new InvalidArgumentException('Request body must be a JSON object.');
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<array-key, mixed>
     */
    private function requiredObject(array $payload, string $field): array
    {
        if (!array_key_exists($field, $payload)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $payload[$field];

        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a JSON object.', $field));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredInt(array $payload, string $field): int
    {
        if (!array_key_exists($field, $payload)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $payload[$field];

        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be an integer.', $field));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $field): string
    {
        if (!array_key_exists($field, $payload)) {
            throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field));
        }

        $value = $payload[$field];

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }
}
