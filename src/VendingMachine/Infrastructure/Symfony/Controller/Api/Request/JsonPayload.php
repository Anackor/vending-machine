<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Symfony\Controller\Api\Request;

use JsonException;
use Symfony\Component\HttpFoundation\Request;
use VendingMachine\Infrastructure\Symfony\Controller\Api\Exception\InvalidMachineJsonRequest;

/**
 * Validated HTTP JSON object payload.
 *
 * This class represents the minimum shared contract for incoming JSON: the body
 * must be either empty or an object, never a list or scalar. Keeping this as a
 * small infrastructure value avoids repeating low-level JSON checks in every
 * endpoint. Symfony argument resolvers were considered, but an explicit helper
 * keeps the challenge code straightforward and converts parser failures into a
 * transport-specific exception before Application is called.
 */
final readonly class JsonPayload
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private array $values,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $content = trim($request->getContent());

        if ($content === '') {
            return new self([]);
        }

        try {
            $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidMachineJsonRequest('Request body must contain valid JSON.', previous: $exception);
        }

        if (!is_array($payload) || ($payload !== [] && array_is_list($payload))) {
            throw new InvalidMachineJsonRequest('Request body must be a JSON object.');
        }

        /** @var array<string, mixed> $payload */
        return new self($payload);
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->values);
    }

    public function value(string $field): mixed
    {
        return $this->values[$field] ?? null;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function requiredObject(string $field): array
    {
        if (!$this->has($field)) {
            throw new InvalidMachineJsonRequest(sprintf('Field "%s" is required.', $field));
        }

        $value = $this->value($field);

        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw new InvalidMachineJsonRequest(sprintf('Field "%s" must be a JSON object.', $field));
        }

        return $value;
    }

    public function requiredString(string $field): string
    {
        if (!$this->has($field)) {
            throw new InvalidMachineJsonRequest(sprintf('Field "%s" is required.', $field));
        }

        $value = $this->value($field);

        if (!is_string($value)) {
            throw new InvalidMachineJsonRequest(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }
}
